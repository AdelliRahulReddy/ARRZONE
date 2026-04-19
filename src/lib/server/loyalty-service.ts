import "server-only";
import { createHash, randomUUID } from "node:crypto";
import type {
  DocumentReference,
  Query,
} from "firebase-admin/firestore";
import {
  RAPID_PURCHASE_WINDOW_SECONDS,
  SECURITY_EVENT_TYPES,
} from "@/lib/constants";
import { getUnlockDelta, rebuildSummary } from "@/lib/domain/loyalty";
import { appEnv } from "@/lib/env";
import {
  COLLECTIONS,
  activeMembershipLookupId,
  idempotencyRequestId,
  membershipMergeId,
  nowIso,
  phoneLookupId,
  rewardUnlockEventId,
  staffBranchAssignmentId,
  type ActiveMembershipLookupDoc,
  type BranchCodeLookupDoc,
  type BranchDoc,
  type CustomerDoc,
  type CustomerPhoneLookupDoc,
  type EnrollmentConsentDoc,
  type IdempotencyRequestDoc,
  type LedgerEventDoc,
  type MemberPassDoc,
  type MembershipDoc,
  type MembershipMergeDoc,
  type MembershipSummaryDoc,
  type PlatformAdminUserDoc,
  type PlanDoc,
  type PlanVersionDoc,
  type RedeemTokenDoc,
  type SecurityEventDoc,
  type StaffBranchAssignmentDoc,
  type StaffStatus,
  type StaffUserDoc,
  type TenantDoc,
} from "@/lib/firebase/model";
import { normalizeEmailAddress } from "@/lib/email";
import { maskPhoneNumber, normalizePhoneNumber } from "@/lib/phone";
import { buildRedeemQrPayload, parseScanPayload } from "@/lib/qr";
import type { PlatformActor, StaffActor } from "@/lib/server/auth";
import { assertBranchAccess } from "@/lib/server/auth";
import type { AppDatabase, AppTransaction } from "@/lib/server/db";
import { requireDb, withTenantTransaction } from "@/lib/server/db";
import { AppError, invariant } from "@/lib/server/errors";
import { signPassToken, verifyPassToken } from "@/lib/server/pass-token";
import {
  createRedeemTokenRecord,
  getRedeemTokenDisposition,
  hashRedeemToken,
} from "@/lib/server/redeem-token";
import {
  assertRateLimit,
  hashIpAddress,
  recordSecurityEvent,
  type SecurityContext,
} from "@/lib/server/security";

type IdempotentResult<T> = {
  replayed: boolean;
  payload: T;
};

type MembershipView = {
  membershipId: string;
  tenantId: string;
  branchId: string;
  customerId: string;
  customerName: string;
  maskedPhone: string;
  planName: string;
  planId: string;
  planVersionId: string;
  thresholdCount: number;
  rewardCreditCount: number;
  applicableBranchIds: string[];
  status: MembershipDoc["status"];
  activePassId: string | null;
  activePassVersion: number | null;
  currentRedeemTokenId: string | null;
  summary: MembershipSummaryDoc;
};

type StaffPerformanceSnapshot = {
  id: string;
  fullName: string;
  email: string;
  role: StaffUserDoc["role"];
  status: StaffUserDoc["status"];
  primaryBranchId: string | null;
  primaryBranchName: string | null;
  branchIds: string[];
  branchNames: string[];
  purchaseAdds: number;
  rewardsRedeemed: number;
  reversals: number;
  totalActions: number;
  lastActionAt: string | null;
  canManageStatus: boolean;
  isCurrentUser: boolean;
};

type BranchPerformanceSnapshot = {
  id: string;
  code: string;
  name: string;
  activeMembers: number;
  purchaseAdds: number;
  rewardsRedeemed: number;
  totalActions: number;
  staffCount: number;
  managerCount: number;
  counterStaffCount: number;
  lastActivityAt: string | null;
};

type MemberHistorySnapshot = {
  memberName: string;
  maskedPhone: string;
  planName: string;
  rewardBalance: number;
  purchaseCount: number;
  entries: Array<{
    id: string;
    eventType: LedgerEventDoc["eventType"];
    createdAt: string;
    quantity: number;
    reasonCode: string | null;
    source: string;
    branchName: string | null;
  }>;
};

type BusinessAdminDirectoryEntry = {
  id: string;
  tenantId: string;
  tenantName: string;
  tenantSlug: string;
  fullName: string;
  email: string;
  status: StaffUserDoc["status"];
  primaryBranchName: string | null;
  branchCount: number;
  authLinked: boolean;
  createdAt: string;
};

function hashPayload(payload: unknown) {
  return createHash("sha256").update(JSON.stringify(payload)).digest("hex");
}

function slugifyTenant(value: string) {
  return value
    .trim()
    .toLowerCase()
    .replace(/[^a-z0-9]+/g, "-")
    .replace(/^-+|-+$/g, "");
}

function passUrl(token: string) {
  return `${appEnv.appUrl}/pass/${token}`;
}

function assertTenantScope(actor: StaffActor, tenantId: string) {
  if (actor.tenantId !== tenantId) {
    throw new AppError(
      "You do not have access to this tenant.",
      403,
      "TENANT_FORBIDDEN",
    );
  }
}

async function readDoc<T>(
  ref: DocumentReference,
  tx?: AppTransaction,
) {
  const snapshot = tx ? await tx.get(ref) : await ref.get();
  return snapshot.exists ? (snapshot.data() as T) : null;
}

async function readQuery<T>(
  query: Query,
  tx?: AppTransaction,
) {
  const snapshot = tx ? await tx.get(query) : await query.get();
  return snapshot.docs.map((doc) => doc.data() as T);
}

function defaultSummary(membership: MembershipDoc): MembershipSummaryDoc {
  return {
    id: membership.id,
    membershipId: membership.id,
    tenantId: membership.tenantId,
    purchaseCount: 0,
    rewardEarnedCount: 0,
    rewardRedeemedCount: 0,
    rewardBalance: 0,
    lastActivityAt: null,
    updatedAt: nowIso(),
    lineageMembershipIds: getLineageMembershipIds(membership),
    canonicalMembershipId: membership.canonicalMembershipId,
  };
}

function dedupeIds(ids: string[]) {
  return [...new Set(ids)];
}

function branchNamesForStaff(
  staff: StaffUserDoc,
  branchNameById: Map<string, string>,
  visibleBranchIds?: Set<string>,
) {
  return dedupeIds(staff.branchIds ?? [])
    .filter((branchId) => !visibleBranchIds || visibleBranchIds.has(branchId))
    .map((branchId) => branchNameById.get(branchId) ?? branchId)
    .sort((left, right) => left.localeCompare(right));
}

function maxIsoTimestamp(current: string | null, next: string | null | undefined) {
  if (!next) {
    return current;
  }

  if (!current || next > current) {
    return next;
  }

  return current;
}

function buildActionMetrics(events: LedgerEventDoc[]) {
  const staffMetrics = new Map<
    string,
    {
      purchaseAdds: number;
      rewardsRedeemed: number;
      reversals: number;
      totalActions: number;
      lastActionAt: string | null;
    }
  >();
  const branchMetrics = new Map<
    string,
    {
      purchaseAdds: number;
      rewardsRedeemed: number;
      totalActions: number;
      lastActivityAt: string | null;
    }
  >();

  for (const event of events) {
    const isReversal =
      event.eventType === "PURCHASE_REVERSED" ||
      event.eventType === "REWARD_REDEMPTION_REVERSED";
    const isPurchaseAdd = event.eventType === "PURCHASE_ADDED";
    const isRewardRedemption = event.eventType === "REWARD_REDEEMED";
    const countsAsStaffAction = Boolean(event.createdByStaffUserId);

    if (event.createdByStaffUserId) {
      const current = staffMetrics.get(event.createdByStaffUserId) ?? {
        purchaseAdds: 0,
        rewardsRedeemed: 0,
        reversals: 0,
        totalActions: 0,
        lastActionAt: null,
      };

      current.totalActions += countsAsStaffAction ? 1 : 0;
      current.purchaseAdds += isPurchaseAdd ? event.quantity : 0;
      current.rewardsRedeemed += isRewardRedemption ? event.quantity : 0;
      current.reversals += isReversal ? event.quantity : 0;
      current.lastActionAt = maxIsoTimestamp(current.lastActionAt, event.createdAt);
      staffMetrics.set(event.createdByStaffUserId, current);
    }

    if (event.branchId) {
      const current = branchMetrics.get(event.branchId) ?? {
        purchaseAdds: 0,
        rewardsRedeemed: 0,
        totalActions: 0,
        lastActivityAt: null,
      };

      current.totalActions += countsAsStaffAction ? 1 : 0;
      current.purchaseAdds += isPurchaseAdd ? event.quantity : 0;
      current.rewardsRedeemed += isRewardRedemption ? event.quantity : 0;
      current.lastActivityAt = maxIsoTimestamp(current.lastActivityAt, event.createdAt);
      branchMetrics.set(event.branchId, current);
    }
  }

  return { staffMetrics, branchMetrics };
}

function canViewStaffFromOperations(actor: StaffActor, staff: StaffUserDoc) {
  if (actor.role === "MERCHANT_ADMIN") {
    return staff.tenantId === actor.tenantId;
  }

  if (actor.role === "MANAGER") {
    return (
      staff.role !== "MERCHANT_ADMIN" &&
      dedupeIds(staff.branchIds ?? []).some((branchId) => actor.branchIds.includes(branchId))
    );
  }

  return false;
}

function canManageStaffStatus(actor: StaffActor, staff: StaffUserDoc) {
  if (staff.id === actor.staffUserId) {
    return false;
  }

  if (actor.role === "MERCHANT_ADMIN") {
    return staff.role !== "MERCHANT_ADMIN";
  }

  if (actor.role === "MANAGER") {
    return (
      staff.role === "CASHIER" &&
      dedupeIds(staff.branchIds ?? []).some((branchId) => actor.branchIds.includes(branchId))
    );
  }

  return false;
}

function buildStaffPerformanceSnapshots(input: {
  actor: StaffActor;
  staffUsers: StaffUserDoc[];
  branchNameById: Map<string, string>;
  events: LedgerEventDoc[];
}) {
  const { actor, staffUsers, branchNameById, events } = input;
  const visibleBranchIds =
    actor.role === "MERCHANT_ADMIN" ? null : new Set(actor.branchIds);
  const { staffMetrics } = buildActionMetrics(events);

  return staffUsers
    .filter((staff) => canViewStaffFromOperations(actor, staff))
    .map((staff) => {
      const branchNames = branchNamesForStaff(staff, branchNameById, visibleBranchIds ?? undefined);
      const metrics = staffMetrics.get(staff.id) ?? {
        purchaseAdds: 0,
        rewardsRedeemed: 0,
        reversals: 0,
        totalActions: 0,
        lastActionAt: null,
      };

      return {
        id: staff.id,
        fullName: staff.fullName,
        email: staff.email,
        role: staff.role,
        status: staff.status,
        primaryBranchId: staff.primaryBranchId,
        primaryBranchName: staff.primaryBranchId
          ? branchNameById.get(staff.primaryBranchId) ?? staff.primaryBranchId
          : null,
        branchIds: visibleBranchIds
          ? dedupeIds(staff.branchIds ?? []).filter((branchId) => visibleBranchIds.has(branchId))
          : dedupeIds(staff.branchIds ?? []),
        branchNames,
        purchaseAdds: metrics.purchaseAdds,
        rewardsRedeemed: metrics.rewardsRedeemed,
        reversals: metrics.reversals,
        totalActions: metrics.totalActions,
        lastActionAt: metrics.lastActionAt,
        canManageStatus: canManageStaffStatus(actor, staff),
        isCurrentUser: staff.id === actor.staffUserId,
      } satisfies StaffPerformanceSnapshot;
    })
    .sort((left, right) => left.fullName.localeCompare(right.fullName));
}

function buildBranchPerformanceSnapshots(input: {
  branches: Array<{ id: string; code: string; name: string }>;
  memberships: MembershipDoc[];
  staffUsers: StaffUserDoc[];
  events: LedgerEventDoc[];
}) {
  const { branches, memberships, staffUsers, events } = input;
  const branchIds = new Set(branches.map((branch) => branch.id));
  const { branchMetrics } = buildActionMetrics(
    events.filter((event) => (event.branchId ? branchIds.has(event.branchId) : false)),
  );

  return branches.map((branch) => {
    const activeMembers = memberships.filter(
      (membership) =>
        membership.status === "ACTIVE" && membership.enrolledBranchId === branch.id,
    ).length;
    const assignedStaff = staffUsers.filter((staff) =>
      dedupeIds(staff.branchIds ?? []).includes(branch.id),
    );
    const metrics = branchMetrics.get(branch.id) ?? {
      purchaseAdds: 0,
      rewardsRedeemed: 0,
      totalActions: 0,
      lastActivityAt: null,
    };

    return {
      id: branch.id,
      code: branch.code,
      name: branch.name,
      activeMembers,
      purchaseAdds: metrics.purchaseAdds,
      rewardsRedeemed: metrics.rewardsRedeemed,
      totalActions: metrics.totalActions,
      staffCount: assignedStaff.length,
      managerCount: assignedStaff.filter((staff) => staff.role === "MANAGER").length,
      counterStaffCount: assignedStaff.filter((staff) => staff.role === "CASHIER").length,
      lastActivityAt: metrics.lastActivityAt,
    } satisfies BranchPerformanceSnapshot;
  });
}

async function assertStaffEmailAvailable(
  db: AppDatabase,
  emailNormalized: string,
  tx?: AppTransaction,
  excludeStaffUserId?: string,
) {
  const matches = await readQuery<StaffUserDoc>(
    db.collection(COLLECTIONS.staffUsers).where("emailNormalized", "==", emailNormalized),
    tx,
  );

  const conflict = matches.find((staff) => staff.id !== excludeStaffUserId);
  invariant(
    !conflict,
    "A staff account with this email already exists.",
    409,
    "STAFF_EMAIL_CONFLICT",
  );
}

async function assertPlatformAdminEmailAvailable(
  db: AppDatabase,
  emailNormalized: string,
  tx?: AppTransaction,
  excludePlatformAdminUserId?: string,
) {
  const matches = await readQuery<PlatformAdminUserDoc>(
    db
      .collection(COLLECTIONS.platformAdminUsers)
      .where("emailNormalized", "==", emailNormalized),
    tx,
  );

  const conflict = matches.find((platformAdmin) => platformAdmin.id !== excludePlatformAdminUserId);
  invariant(
    !conflict,
    "A platform admin with this email already exists.",
    409,
    "PLATFORM_ADMIN_EMAIL_CONFLICT",
  );
}

async function listTenantBranchIds(
  db: AppDatabase,
  tenantId: string,
  tx?: AppTransaction,
) {
  const branches = await readQuery<BranchDoc>(
    db.collection(COLLECTIONS.branches).where("tenantId", "==", tenantId),
    tx,
  );

  return branches
    .map((branch) => branch.id)
    .sort((left, right) => left.localeCompare(right));
}

async function syncStaffBranchAssignments(input: {
  database: AppDatabase;
  tx: AppTransaction;
  tenantId: string;
  staffUserId: string;
  branchIds: string[];
  primaryBranchId: string | null;
}) {
  const existingAssignments = await readQuery<StaffBranchAssignmentDoc>(
    input.database
      .collection(COLLECTIONS.staffBranchAssignments)
      .where("staffUserId", "==", input.staffUserId),
    input.tx,
  );

  const existingAssignmentsById = new Map(
    existingAssignments.map((assignment) => [assignment.id, assignment]),
  );
  const timestamp = nowIso();

  for (const branchId of dedupeIds(input.branchIds)) {
    const assignmentId = staffBranchAssignmentId(input.staffUserId, branchId);
    const existingAssignment = existingAssignmentsById.get(assignmentId);
    input.tx.set(
      input.database.collection(COLLECTIONS.staffBranchAssignments).doc(assignmentId),
      {
        id: assignmentId,
        tenantId: input.tenantId,
        staffUserId: input.staffUserId,
        branchId,
        isPrimary: branchId === input.primaryBranchId,
        createdAt: existingAssignment?.createdAt ?? timestamp,
      } satisfies StaffBranchAssignmentDoc,
    );
    existingAssignmentsById.delete(assignmentId);
  }

  for (const staleAssignment of existingAssignmentsById.values()) {
    input.tx.delete(
      input.database
        .collection(COLLECTIONS.staffBranchAssignments)
        .doc(staleAssignment.id),
    );
  }
}

function getLineageMembershipIds(membership: MembershipDoc) {
  if (membership.canonicalMembershipId !== membership.id) {
    return [membership.id];
  }

  return dedupeIds([membership.id, ...(membership.mergedMembershipIds ?? [])]);
}

function buildMembershipView(
  membership: Omit<MembershipView, "summary" | "maskedPhone"> & {
    normalizedPhone: string;
  },
  summary: MembershipSummaryDoc,
) {
  return {
    membershipId: membership.membershipId,
    tenantId: membership.tenantId,
    branchId: membership.branchId,
    customerId: membership.customerId,
    customerName: membership.customerName,
    maskedPhone: maskPhoneNumber(membership.normalizedPhone),
    planName: membership.planName,
    planId: membership.planId,
    planVersionId: membership.planVersionId,
    thresholdCount: membership.thresholdCount,
    rewardCreditCount: membership.rewardCreditCount,
    applicableBranchIds: membership.applicableBranchIds,
    status: membership.status,
    activePassId: membership.activePassId,
    activePassVersion: membership.activePassVersion,
    currentRedeemTokenId: membership.currentRedeemTokenId,
    summary: {
      ...summary,
    },
  };
}

async function getBranchByCode(
  db: AppDatabase,
  branchCode: string,
  tx?: AppTransaction,
) {
  const lookup = await readDoc<BranchCodeLookupDoc>(
    db.collection(COLLECTIONS.branchCodeLookups).doc(branchCode),
    tx,
  );

  if (!lookup || lookup.status !== "ACTIVE") {
    return null;
  }

  const branch = await readDoc<BranchDoc>(
    db.collection(COLLECTIONS.branches).doc(lookup.branchId),
    tx,
  );

  if (!branch || branch.status !== "ACTIVE") {
    return null;
  }

  return branch;
}

export async function getEnrollmentBranchContext(branchCode: string) {
  const db = requireDb();
  const branch = await getBranchByCode(db, branchCode);
  invariant(branch, "Branch not found.", 404, "BRANCH_NOT_FOUND");

  const planRows = await db
    .collection(COLLECTIONS.plans)
    .where("tenantId", "==", branch.tenantId)
    .get();

  const plans = planRows.docs
    .map((doc) => doc.data() as PlanDoc)
    .filter(
      (plan) =>
        plan.status === "ACTIVE" &&
        (plan.applicableBranchIds.length === 0 ||
          plan.applicableBranchIds.includes(branch.id)),
    )
    .sort((left, right) => left.name.localeCompare(right.name))
    .map((plan) => ({
      id: plan.id,
      name: plan.name,
      eligibleLabel: plan.eligibleLabel,
      thresholdCount: plan.thresholdCount,
      rewardCreditCount: plan.rewardCreditCount,
    }));

  return {
    branch: {
      id: branch.id,
      tenantId: branch.tenantId,
      code: branch.code,
      name: branch.name,
      timezone: branch.timezone,
    },
    plans,
  };
}

async function getMembershipDoc(
  db: AppDatabase,
  membershipId: string,
  tx?: AppTransaction,
) {
  return readDoc<MembershipDoc>(
    db.collection(COLLECTIONS.memberships).doc(membershipId),
    tx,
  );
}

async function getPassDoc(
  db: AppDatabase,
  passId: string,
  tx?: AppTransaction,
) {
  return readDoc<MemberPassDoc>(
    db.collection(COLLECTIONS.memberPasses).doc(passId),
    tx,
  );
}

async function getRedeemTokenDoc(
  db: AppDatabase,
  tokenId: string,
  tx?: AppTransaction,
) {
  return readDoc<RedeemTokenDoc>(
    db.collection(COLLECTIONS.redeemTokens).doc(tokenId),
    tx,
  );
}

async function getMembershipWithPlan(
  db: AppDatabase,
  membershipId: string,
  tx?: AppTransaction,
) {
  const membership = await getMembershipDoc(db, membershipId, tx);
  invariant(membership, "Membership not found.", 404, "MEMBERSHIP_NOT_FOUND");

  const customer = await readDoc<CustomerDoc>(
    db.collection(COLLECTIONS.customers).doc(membership.customerId),
    tx,
  );
  const planVersion = await readDoc<PlanVersionDoc>(
    db.collection(COLLECTIONS.planVersions).doc(membership.planVersionId),
    tx,
  );
  const plan = await readDoc<PlanDoc>(
    db.collection(COLLECTIONS.plans).doc(membership.planId),
    tx,
  );

  invariant(customer, "Customer not found.", 500, "CUSTOMER_NOT_FOUND");
  invariant(
    planVersion,
    "Plan version not found.",
    500,
    "PLAN_VERSION_NOT_FOUND",
  );
  invariant(plan, "Plan not found.", 500, "PLAN_NOT_FOUND");

  return {
    membershipId: membership.id,
    tenantId: membership.tenantId,
    status: membership.status,
    customerId: membership.customerId,
    branchId: membership.enrolledBranchId,
    planId: membership.planId,
    planVersionId: membership.planVersionId,
    thresholdCount: planVersion.thresholdCount,
    rewardCreditCount: planVersion.rewardCreditCount,
    customerName: customer.fullName,
    normalizedPhone: customer.normalizedPhone,
    planName: plan.name,
    applicableBranchIds: plan.applicableBranchIds,
    activePassId: membership.activePassId,
    activePassVersion: membership.activePassVersion,
    currentRedeemTokenId: membership.currentRedeemTokenId,
  };
}

async function listLedgerEventsForMemberships(
  db: AppDatabase,
  membershipIds: string[],
  tx?: AppTransaction,
) {
  const rows: LedgerEventDoc[] = [];
  for (const membershipId of membershipIds) {
    const membershipRows = await readQuery<LedgerEventDoc>(
      db
        .collection(COLLECTIONS.ledgerEvents)
        .where("membershipId", "==", membershipId)
        .orderBy("createdAt", "asc"),
      tx,
    );
    rows.push(...membershipRows);
  }

  return rows.sort((left, right) => left.createdAt.localeCompare(right.createdAt));
}

async function rebuildAndPersistSummary(
  db: AppDatabase,
  membershipId: string,
  tx?: AppTransaction,
) {
  const membership = await getMembershipDoc(db, membershipId, tx);
  invariant(membership, "Membership not found.", 404, "MEMBERSHIP_NOT_FOUND");

  const events = await listLedgerEventsForMemberships(
    db,
    getLineageMembershipIds(membership),
    tx,
  );
  const summary = rebuildSummary(
    events.map((event) => ({
      eventType: event.eventType,
      quantity: event.quantity,
    })),
  );
  const latestActivityAt = events.at(-1)?.createdAt ?? null;
  const summaryDoc: MembershipSummaryDoc = {
    id: membership.id,
    membershipId: membership.id,
    tenantId: membership.tenantId,
    purchaseCount: summary.purchaseCount,
    rewardEarnedCount: summary.rewardEarnedCount,
    rewardRedeemedCount: summary.rewardRedeemedCount,
    rewardBalance: summary.rewardBalance,
    lastActivityAt: latestActivityAt,
    updatedAt: nowIso(),
    lineageMembershipIds: getLineageMembershipIds(membership),
    canonicalMembershipId: membership.canonicalMembershipId,
  };

  const ref = db.collection(COLLECTIONS.membershipSummaries).doc(membership.id);
  if (tx) {
    tx.set(ref, summaryDoc);
  } else {
    await ref.set(summaryDoc);
  }

  return summaryDoc;
}

async function getMembershipSummary(
  db: AppDatabase,
  membershipId: string,
  tx?: AppTransaction,
) {
  const membership = await getMembershipDoc(db, membershipId, tx);
  invariant(membership, "Membership not found.", 404, "MEMBERSHIP_NOT_FOUND");

  const ref = db.collection(COLLECTIONS.membershipSummaries).doc(membershipId);
  const existing = await readDoc<MembershipSummaryDoc>(ref, tx);
  if (existing) {
    return existing;
  }

  if (!tx) {
    return rebuildAndPersistSummary(db, membershipId);
  }

  return rebuildAndPersistSummary(db, membershipId, tx);
}

async function getActivePassForMembership(
  db: AppDatabase,
  membershipId: string,
  tx?: AppTransaction,
) {
  const membership = await getMembershipDoc(db, membershipId, tx);
  if (!membership?.activePassId) {
    return null;
  }

  const pass = await getPassDoc(db, membership.activePassId, tx);
  if (!pass || pass.status !== "ACTIVE") {
    return null;
  }

  return pass;
}

async function revokeRedeemTokenIfLive(
  db: AppDatabase,
  tx: AppTransaction,
  tokenId: string | null | undefined,
) {
  if (!tokenId) {
    return;
  }

  const tokenRef = db.collection(COLLECTIONS.redeemTokens).doc(tokenId);
  const token = await readDoc<RedeemTokenDoc>(tokenRef, tx);
  if (!token || token.revokedAt || token.consumedAt) {
    return;
  }

  tx.set(
    tokenRef,
    {
      ...token,
      revokedAt: nowIso(),
      updatedAt: nowIso(),
    } satisfies RedeemTokenDoc,
  );
}

async function revokePass(
  db: AppDatabase,
  tx: AppTransaction,
  pass: MemberPassDoc | null,
) {
  if (!pass || pass.status !== "ACTIVE") {
    return;
  }

  await revokeRedeemTokenIfLive(db, tx, pass.currentRedeemTokenId);
  tx.set(
    db.collection(COLLECTIONS.memberPasses).doc(pass.id),
    {
      ...pass,
      status: "REVOKED",
      revokedAt: nowIso(),
      currentRedeemTokenId: null,
      updatedAt: nowIso(),
    } satisfies MemberPassDoc,
  );
}

async function insertLedgerEvent(
  db: AppDatabase,
  tx: AppTransaction,
  input: {
    tenantId: string;
    branchId?: string | null;
    membershipId: string;
    customerId: string;
    eventType: LedgerEventDoc["eventType"];
    quantity: number;
    unlockCycle?: number | null;
    reasonCode?: string | null;
    source: string;
    idempotencyKey?: string | null;
    createdByStaffUserId?: string | null;
    metadata: Record<string, unknown>;
    id?: string;
  },
) {
  const event: LedgerEventDoc = {
    id: input.id ?? randomUUID(),
    tenantId: input.tenantId,
    branchId: input.branchId ?? null,
    membershipId: input.membershipId,
    customerId: input.customerId,
    eventType: input.eventType,
    quantity: input.quantity,
    unlockCycle: input.unlockCycle ?? null,
    reasonCode: input.reasonCode ?? null,
    source: input.source,
    idempotencyKey: input.idempotencyKey ?? null,
    createdByStaffUserId: input.createdByStaffUserId ?? null,
    metadata: input.metadata,
    createdAt: nowIso(),
  };

  tx.set(db.collection(COLLECTIONS.ledgerEvents).doc(event.id), event);
  return event;
}

async function ensureBranchIdsBelongToTenant(
  db: AppDatabase,
  tenantId: string,
  branchIds: string[],
  tx?: AppTransaction,
) {
  for (const branchId of dedupeIds(branchIds)) {
    const branch = await readDoc<BranchDoc>(
      db.collection(COLLECTIONS.branches).doc(branchId),
      tx,
    );
    invariant(branch, "Branch not found.", 404, "BRANCH_NOT_FOUND");
    invariant(
      branch.tenantId === tenantId,
      "Branch belongs to another tenant.",
      400,
      "BRANCH_TENANT_MISMATCH",
    );
  }
}

async function createPass(
  db: AppDatabase,
  tx: AppTransaction,
  membership: MembershipDoc,
  actor: StaffActor | undefined,
  eventType: "PASS_ISSUED" | "PASS_REISSUED",
) {
  const issuedAt = nowIso();
  const pass: MemberPassDoc = {
    id: randomUUID(),
    tenantId: membership.tenantId,
    membershipId: membership.id,
    passVersion: membership.lastIssuedPassVersion + 1,
    tokenId: randomUUID(),
    status: "ACTIVE",
    currentRedeemTokenId: null,
    issuedAt,
    revokedAt: null,
    updatedAt: issuedAt,
  };

  tx.set(db.collection(COLLECTIONS.memberPasses).doc(pass.id), pass);
  tx.set(
    db.collection(COLLECTIONS.memberships).doc(membership.id),
    {
      ...membership,
      activePassId: pass.id,
      activePassVersion: pass.passVersion,
      currentRedeemTokenId: null,
      currentRedeemTokenPassId: pass.id,
      lastIssuedPassVersion: pass.passVersion,
      updatedAt: issuedAt,
    } satisfies MembershipDoc,
  );

  const token = await signPassToken({
    type: "PASS",
    passId: pass.id,
    membershipId: membership.id,
    tenantId: membership.tenantId,
    passVersion: pass.passVersion,
  });

  await insertLedgerEvent(db, tx, {
    tenantId: membership.tenantId,
    branchId: membership.enrolledBranchId,
    membershipId: membership.id,
    customerId: membership.customerId,
    eventType,
    quantity: 1,
    source: "SYSTEM",
    createdByStaffUserId: actor?.staffUserId ?? null,
    metadata: {
      passId: pass.id,
      passVersion: pass.passVersion,
    },
  });

  return {
    pass,
    token,
    passUrl: passUrl(token),
  };
}

async function readIdempotentResponse<T extends Record<string, unknown>>(
  db: AppDatabase,
  tenantId: string,
  operation: string,
  idempotencyKey: string,
  requestPayload: unknown,
  tx?: AppTransaction,
) {
  const ref = db
    .collection(COLLECTIONS.idempotencyRequests)
    .doc(idempotencyRequestId(tenantId, operation, idempotencyKey));
  const existing = await readDoc<IdempotencyRequestDoc>(ref, tx);
  if (!existing) {
    return null;
  }

  const requestHash = hashPayload(requestPayload);
  if (existing.requestHash !== requestHash) {
    throw new AppError(
      "Idempotency key was reused with a different payload.",
      409,
      "IDEMPOTENCY_CONFLICT",
    );
  }

  return existing.responseBody as T;
}

async function storeIdempotentResponse(
  db: AppDatabase,
  tx: AppTransaction,
  tenantId: string,
  operation: string,
  idempotencyKey: string,
  requestPayload: unknown,
  responseBody: Record<string, unknown>,
  responseStatus = 200,
) {
  const doc: IdempotencyRequestDoc = {
    id: idempotencyRequestId(tenantId, operation, idempotencyKey),
    tenantId,
    operation,
    idempotencyKey,
    requestHash: hashPayload(requestPayload),
    responseStatus,
    responseBody,
    createdAt: nowIso(),
  };

  tx.set(
    db.collection(COLLECTIONS.idempotencyRequests).doc(doc.id),
    doc,
  );
}

async function runIdempotentOperation<T extends Record<string, unknown>>(input: {
  tenantId: string;
  operation: string;
  idempotencyKey: string;
  requestPayload: unknown;
  actor?: StaffActor;
  run: (tx: AppTransaction, db: AppDatabase) => Promise<T>;
}) {
  const db = requireDb();
  const existing = await readIdempotentResponse<T>(
    db,
    input.tenantId,
    input.operation,
    input.idempotencyKey,
    input.requestPayload,
  );

  if (existing) {
    return { replayed: true, payload: existing } satisfies IdempotentResult<T>;
  }

  return withTenantTransaction(
    {
      tenantId: input.tenantId,
      actorId: input.actor?.staffUserId,
      actorRole: input.actor?.role,
    },
    async (tx, database) => {
      const replayed = await readIdempotentResponse<T>(
        database,
        input.tenantId,
        input.operation,
        input.idempotencyKey,
        input.requestPayload,
        tx,
      );

      if (replayed) {
        return { replayed: true, payload: replayed } satisfies IdempotentResult<T>;
      }

      const payload = await input.run(tx, database);
      await storeIdempotentResponse(
        database,
        tx,
        input.tenantId,
        input.operation,
        input.idempotencyKey,
        input.requestPayload,
        payload,
      );

      return { replayed: false, payload } satisfies IdempotentResult<T>;
    },
  );
}

async function getLatestUnreversedSourceEvent(
  db: AppDatabase,
  membership: MembershipDoc,
  sourceEventType: "PURCHASE_ADDED" | "REWARD_REDEEMED",
  reversalEventType: "PURCHASE_REVERSED" | "REWARD_REDEMPTION_REVERSED",
  tx?: AppTransaction,
) {
  const lineageIds = getLineageMembershipIds(membership);
  const sourceEvents: LedgerEventDoc[] = [];
  const reversalEvents: LedgerEventDoc[] = [];

  for (const membershipId of lineageIds) {
    sourceEvents.push(
      ...(await readQuery<LedgerEventDoc>(
        db
          .collection(COLLECTIONS.ledgerEvents)
          .where("membershipId", "==", membershipId)
          .where("eventType", "==", sourceEventType)
          .orderBy("createdAt", "desc"),
        tx,
      )),
    );
    reversalEvents.push(
      ...(await readQuery<LedgerEventDoc>(
        db
          .collection(COLLECTIONS.ledgerEvents)
          .where("membershipId", "==", membershipId)
          .where("eventType", "==", reversalEventType)
          .orderBy("createdAt", "desc"),
        tx,
      )),
    );
  }

  const reversedIds = new Set(
    reversalEvents
      .map((event) => event.metadata.reversedEventId)
      .filter((value): value is string => typeof value === "string"),
  );

  return sourceEvents.find((event) => !reversedIds.has(event.id)) ?? null;
}

export async function createEnrollment(input: {
  branchCode: string;
  planId: string;
  fullName: string;
  phone: string;
  email?: string | null;
  consentVersion: string;
  consentAccepted: boolean;
  ip?: string | null;
}) {
  const db = requireDb();
  const branch = await getBranchByCode(db, input.branchCode);
  invariant(branch, "Branch not found.", 404, "BRANCH_NOT_FOUND");

  const plan = await readDoc<PlanDoc>(
    db.collection(COLLECTIONS.plans).doc(input.planId),
  );
  invariant(plan, "Plan not found.", 404, "PLAN_NOT_FOUND");
  invariant(plan.tenantId === branch.tenantId, "Plan not found.", 404, "PLAN_NOT_FOUND");
  invariant(plan.status === "ACTIVE", "Plan not found.", 404, "PLAN_NOT_FOUND");

  if (
    plan.applicableBranchIds.length > 0 &&
    !plan.applicableBranchIds.includes(branch.id)
  ) {
    throw new AppError(
      "This plan is not available at the selected branch.",
      400,
      "PLAN_BRANCH_MISMATCH",
    );
  }

  const normalizedPhone = normalizePhoneNumber(input.phone);
  const phoneLookupRef = db
    .collection(COLLECTIONS.customerPhoneLookups)
    .doc(phoneLookupId(branch.tenantId, normalizedPhone));

  return withTenantTransaction(
    { tenantId: branch.tenantId },
    async (tx, database) => {
      const currentPlan = await readDoc<PlanDoc>(
        database.collection(COLLECTIONS.plans).doc(plan.id),
        tx,
      );
      invariant(currentPlan, "Plan not found.", 404, "PLAN_NOT_FOUND");
      invariant(
        currentPlan.currentVersionId,
        "The plan does not have an active version snapshot.",
        500,
        "PLAN_VERSION_MISSING",
      );

      const planVersion = await readDoc<PlanVersionDoc>(
        database.collection(COLLECTIONS.planVersions).doc(currentPlan.currentVersionId),
        tx,
      );
      invariant(
        planVersion,
        "The plan does not have an active version snapshot.",
        500,
        "PLAN_VERSION_MISSING",
      );

      const existingPhoneLookup = await readDoc<CustomerPhoneLookupDoc>(
        phoneLookupRef,
        tx,
      );
      let existingCustomer: CustomerDoc | null = null;

      if (existingPhoneLookup) {
        existingCustomer = await readDoc<CustomerDoc>(
          database.collection(COLLECTIONS.customers).doc(existingPhoneLookup.customerId),
          tx,
        );
      }

      const customer =
        existingCustomer ??
        ({
          id: randomUUID(),
          tenantId: branch.tenantId,
          fullName: input.fullName,
          normalizedPhone,
          email: input.email ?? null,
          phoneVerified: false,
          status: "ACTIVE",
          createdAt: nowIso(),
        } satisfies CustomerDoc);
      const shouldCreateCustomer = existingCustomer === null;

      const membershipLookupRef = database
        .collection(COLLECTIONS.activeMembershipLookups)
        .doc(activeMembershipLookupId(branch.tenantId, customer.id, currentPlan.id));
      const membershipLookup = await readDoc<ActiveMembershipLookupDoc>(
        membershipLookupRef,
        tx,
      );

      if (membershipLookup && membershipLookup.status === "ACTIVE") {
        const existingMembership = await getMembershipDoc(
          database,
          membershipLookup.membershipId,
          tx,
        );
        invariant(
          existingMembership,
          "Membership not found.",
          404,
          "MEMBERSHIP_NOT_FOUND",
        );

        let currentPass = await getActivePassForMembership(
          database,
          existingMembership.id,
          tx,
        );
        if (!currentPass) {
          const repairedPass = await createPass(
            database,
            tx,
            existingMembership,
            undefined,
            "PASS_REISSUED",
          );
          currentPass = repairedPass.pass;
          return {
            membershipId: existingMembership.id,
            existingMembership: true,
            passToken: repairedPass.token,
            passUrl: repairedPass.passUrl,
          };
        }

        const token = await signPassToken({
          type: "PASS",
          passId: currentPass.id,
          membershipId: existingMembership.id,
          tenantId: existingMembership.tenantId,
          passVersion: currentPass.passVersion,
        });

        return {
          membershipId: existingMembership.id,
          existingMembership: true,
          passToken: token,
          passUrl: passUrl(token),
        };
      }

      if (shouldCreateCustomer) {
        tx.set(database.collection(COLLECTIONS.customers).doc(customer.id), customer);
        tx.set(
          phoneLookupRef,
          {
            id: phoneLookupRef.id,
            customerId: customer.id,
            tenantId: branch.tenantId,
            normalizedPhone,
            updatedAt: nowIso(),
          } satisfies CustomerPhoneLookupDoc,
        );
      }

      const membership: MembershipDoc = {
        id: randomUUID(),
        tenantId: branch.tenantId,
        customerId: customer.id,
        enrolledBranchId: branch.id,
        planId: currentPlan.id,
        planVersionId: planVersion.id,
        status: "ACTIVE",
        mergedIntoMembershipId: null,
        canonicalMembershipId: "",
        mergedMembershipIds: [],
        startedAt: nowIso(),
        activePassId: null,
        activePassVersion: null,
        currentRedeemTokenId: null,
        currentRedeemTokenPassId: null,
        lastIssuedPassVersion: 0,
        updatedAt: nowIso(),
      };
      membership.canonicalMembershipId = membership.id;

      tx.set(database.collection(COLLECTIONS.memberships).doc(membership.id), membership);
      tx.set(
        membershipLookupRef,
        {
          id: membershipLookupRef.id,
          membershipId: membership.id,
          tenantId: branch.tenantId,
          customerId: customer.id,
          planId: currentPlan.id,
          status: "ACTIVE",
          updatedAt: nowIso(),
        } satisfies ActiveMembershipLookupDoc,
      );
      tx.set(
        database.collection(COLLECTIONS.membershipSummaries).doc(membership.id),
        defaultSummary(membership),
      );

      await insertLedgerEvent(database, tx, {
        tenantId: branch.tenantId,
        branchId: branch.id,
        membershipId: membership.id,
        customerId: customer.id,
        eventType: "MEMBERSHIP_CREATED",
        quantity: 1,
        source: "ENROLLMENT",
        metadata: {
          branchCode: branch.code,
          planId: currentPlan.id,
        },
      });

      const consent: EnrollmentConsentDoc = {
        id: randomUUID(),
        tenantId: branch.tenantId,
        customerId: customer.id,
        membershipId: membership.id,
        consentVersion: input.consentVersion,
        consentedAt: nowIso(),
        ipHash: hashIpAddress(input.ip),
      };
      tx.set(
        database.collection(COLLECTIONS.enrollmentConsents).doc(consent.id),
        consent,
      );

      const issuedPass = await createPass(
        database,
        tx,
        membership,
        undefined,
        "PASS_ISSUED",
      );

      return {
        membershipId: membership.id,
        existingMembership: false,
        passToken: issuedPass.token,
        passUrl: issuedPass.passUrl,
      };
    },
  );
}

export async function getPassSnapshot(passToken: string) {
  const claims = await verifyPassToken(passToken);
  const db = requireDb();
  const pass = await getPassDoc(db, claims.passId);
  invariant(pass, "Pass is invalid or revoked.", 404, "PASS_INVALID");

  const membership = await getMembershipDoc(db, claims.membershipId);
  invariant(membership, "Pass is invalid or revoked.", 404, "PASS_INVALID");
  invariant(
    membership.tenantId === claims.tenantId &&
      membership.activePassId === claims.passId &&
      membership.activePassVersion === claims.passVersion &&
      membership.status === "ACTIVE" &&
      pass.membershipId === claims.membershipId &&
      pass.tenantId === claims.tenantId &&
      pass.passVersion === claims.passVersion &&
      pass.status === "ACTIVE",
    "Pass is invalid or revoked.",
    404,
    "PASS_INVALID",
  );

  const membershipView = await getMembershipWithPlan(db, claims.membershipId);
  const summary = await getMembershipSummary(db, claims.membershipId);
  const branch = await readDoc<BranchDoc>(
    db.collection(COLLECTIONS.branches).doc(membershipView.branchId),
  );

  return {
    pass,
    passToken,
    passUrl: passUrl(passToken),
    branchCode: branch?.code ?? null,
    branchName: branch?.name ?? membershipView.branchId,
    ...buildMembershipView(membershipView, summary),
  };
}

export async function getPassHistory(passToken: string) {
  const snapshot = await getPassSnapshot(passToken);
  const db = requireDb();
  const membership = await getMembershipDoc(db, snapshot.membershipId);
  invariant(membership, "Pass is invalid or revoked.", 404, "PASS_INVALID");

  const events = await listLedgerEventsForMemberships(
    db,
    getLineageMembershipIds(membership),
  );
  const branchIds = dedupeIds(
    events
      .map((event) => event.branchId)
      .filter((branchId): branchId is string => typeof branchId === "string"),
  );
  const branchRows = await Promise.all(
    branchIds.map((branchId) =>
      readDoc<BranchDoc>(db.collection(COLLECTIONS.branches).doc(branchId)),
    ),
  );
  const branchNameById = new Map(
    branchRows
      .filter((branch): branch is BranchDoc => Boolean(branch))
      .map((branch) => [branch.id, branch.name]),
  );

  return {
    memberName: snapshot.customerName,
    maskedPhone: snapshot.maskedPhone,
    planName: snapshot.planName,
    rewardBalance: snapshot.summary.rewardBalance,
    purchaseCount: snapshot.summary.purchaseCount,
    entries: [...events]
      .sort((left, right) => right.createdAt.localeCompare(left.createdAt))
      .map((event) => ({
        id: event.id,
        eventType: event.eventType,
        createdAt: event.createdAt,
        quantity: event.quantity,
        reasonCode: event.reasonCode,
        source: event.source,
        branchName: event.branchId ? branchNameById.get(event.branchId) ?? event.branchId : null,
      })),
  } satisfies MemberHistorySnapshot;
}

export async function issueRedeemToken(
  passToken: string,
  context: SecurityContext,
) {
  const claims = await verifyPassToken(passToken);
  const db = requireDb();

  await assertRateLimit(db, "redeemTokenGeneration", {
    ...context,
    tenantId: claims.tenantId,
    eventType: SECURITY_EVENT_TYPES.redeemTokenGenerationAttempt,
    scopeKey: `pass:${claims.passId}`,
    subjectKey: claims.membershipId,
  });

  return withTenantTransaction(
    { tenantId: claims.tenantId },
    async (tx, database) => {
      const pass = await getPassDoc(database, claims.passId, tx);
      const membership = await getMembershipDoc(database, claims.membershipId, tx);
      invariant(pass && membership, "Pass is invalid or revoked.", 404, "PASS_INVALID");
      invariant(
        membership.tenantId === claims.tenantId &&
          membership.activePassId === pass.id &&
          membership.activePassVersion === claims.passVersion &&
          membership.status === "ACTIVE" &&
          pass.status === "ACTIVE" &&
          pass.membershipId === membership.id &&
          pass.passVersion === claims.passVersion,
        "Pass is invalid or revoked.",
        404,
        "PASS_INVALID",
      );

      await revokeRedeemTokenIfLive(database, tx, pass.currentRedeemTokenId);

      const tokenRecord = createRedeemTokenRecord();
      const issuedAt = nowIso();
      const tokenDoc: RedeemTokenDoc = {
        id: tokenRecord.tokenHash,
        tenantId: claims.tenantId,
        passId: pass.id,
        membershipId: membership.id,
        tokenHash: tokenRecord.tokenHash,
        tokenPreview: tokenRecord.preview,
        expiresAt: tokenRecord.expiresAt.toISOString(),
        consumedAt: null,
        revokedAt: null,
        issuedAt,
        updatedAt: issuedAt,
      };

      tx.set(
        database.collection(COLLECTIONS.redeemTokens).doc(tokenDoc.id),
        tokenDoc,
      );
      tx.set(
        database.collection(COLLECTIONS.memberPasses).doc(pass.id),
        {
          ...pass,
          currentRedeemTokenId: tokenDoc.id,
          updatedAt: issuedAt,
        } satisfies MemberPassDoc,
      );
      tx.set(
        database.collection(COLLECTIONS.memberships).doc(membership.id),
        {
          ...membership,
          currentRedeemTokenId: tokenDoc.id,
          currentRedeemTokenPassId: pass.id,
          updatedAt: issuedAt,
        } satisfies MembershipDoc,
      );

      return {
        redeemQrPayload: buildRedeemQrPayload(tokenRecord.rawToken),
        expiresAt: tokenRecord.expiresAt.toISOString(),
      };
    },
  );
}

export async function lookupMembershipByPassPayload(
  rawPayload: string,
  actor: StaffActor,
  context: SecurityContext,
) {
  const db = requireDb();
  await assertRateLimit(db, "qrLookup", {
    ...context,
    tenantId: actor.tenantId,
    branchId: actor.branchIds[0] ?? null,
    staffUserId: actor.staffUserId,
    eventType: SECURITY_EVENT_TYPES.qrLookupAttempt,
    scopeKey: `staff:${actor.staffUserId}`,
    subjectKey: rawPayload.slice(-16),
  });

  const parsed = parseScanPayload(rawPayload);
  if (parsed.type !== "PASS") {
    await recordSecurityEvent(db, {
      ...context,
      tenantId: actor.tenantId,
      staffUserId: actor.staffUserId,
      eventType: SECURITY_EVENT_TYPES.suspiciousActivity,
      scopeKey: `staff:${actor.staffUserId}`,
      metadata: {
        reason: "invalid_lookup_payload",
        payloadType: parsed.type,
      },
    });
    throw new AppError(
      "This payload cannot be used for pass lookup.",
      400,
      "LOOKUP_REQUIRES_PASS",
    );
  }

  const snapshot = await getPassSnapshot(parsed.token);
  assertTenantScope(actor, snapshot.tenantId);
  assertBranchAccess(actor, snapshot.branchId);

  await assertRateLimit(db, "passLookup", {
    ...context,
    tenantId: snapshot.tenantId,
    branchId: snapshot.branchId,
    staffUserId: actor.staffUserId,
    eventType: SECURITY_EVENT_TYPES.passLookupAttempt,
    scopeKey: `membership:${snapshot.membershipId}`,
    subjectKey: snapshot.pass.id,
  });

  return snapshot;
}

export async function searchMembershipsByPhone(
  phone: string,
  actor: StaffActor,
  context: SecurityContext,
) {
  const db = requireDb();
  const normalizedPhone = normalizePhoneNumber(phone);

  await assertRateLimit(db, "phoneSearch", {
    ...context,
    tenantId: actor.tenantId,
    staffUserId: actor.staffUserId,
    eventType: SECURITY_EVENT_TYPES.phoneSearchAttempt,
    scopeKey: `phone:${normalizedPhone}`,
  });

  const phoneLookup = await readDoc<CustomerPhoneLookupDoc>(
    db
      .collection(COLLECTIONS.customerPhoneLookups)
      .doc(phoneLookupId(actor.tenantId, normalizedPhone)),
  );

  if (!phoneLookup) {
    return [];
  }

  const customer = await readDoc<CustomerDoc>(
    db.collection(COLLECTIONS.customers).doc(phoneLookup.customerId),
  );
  if (!customer || customer.status !== "ACTIVE") {
    return [];
  }

  const memberships = await readQuery<MembershipDoc>(
    db
      .collection(COLLECTIONS.memberships)
      .where("tenantId", "==", actor.tenantId)
      .where("customerId", "==", customer.id)
      .where("status", "==", "ACTIVE"),
  );

  const visibleMemberships = memberships.filter(
    (membership) => actor.branchIds.includes(membership.enrolledBranchId),
  );

  const results: Array<ReturnType<typeof buildMembershipView>> = [];
  for (const membership of visibleMemberships) {
    const membershipView = await getMembershipWithPlan(db, membership.id);
    const summary = await getMembershipSummary(db, membership.id);
    results.push(
      buildMembershipView(membershipView, summary),
    );
  }

  return results.map((result) => ({
    membershipId: result.membershipId,
    branchId: result.branchId,
    customerName: result.customerName,
    maskedPhone: result.maskedPhone,
    planName: result.planName,
    summary: {
      purchaseCount: result.summary.purchaseCount,
      rewardBalance: result.summary.rewardBalance,
    },
  }));
}

export async function getMembershipSnapshotForStaff(
  membershipId: string,
  actor: StaffActor,
) {
  const db = requireDb();
  const membership = await getMembershipWithPlan(db, membershipId);
  assertTenantScope(actor, membership.tenantId);
  assertBranchAccess(actor, membership.branchId);
  const summary = await getMembershipSummary(db, membershipId);
  return buildMembershipView(membership, summary);
}

export async function getStaffWorkspaceSnapshot(actor: StaffActor) {
  const db = requireDb();
  const branchRows = await Promise.all(
    actor.branchIds.map((branchId) =>
      readDoc<BranchDoc>(db.collection(COLLECTIONS.branches).doc(branchId)),
    ),
  );

  const accessibleBranches = branchRows
    .filter((branch): branch is BranchDoc => Boolean(branch))
    .sort((left, right) => left.name.localeCompare(right.name))
    .map((branch) => ({
      id: branch.id,
      code: branch.code,
      name: branch.name,
      timezone: branch.timezone,
    }));

  const branchNameById = new Map(
    accessibleBranches.map((branch) => [branch.id, branch.name]),
  );

  const membershipRows = await readQuery<MembershipDoc>(
    db.collection(COLLECTIONS.memberships).where("tenantId", "==", actor.tenantId),
  );

  const visibleMemberships = membershipRows
    .filter(
      (membership) =>
        membership.status === "ACTIVE" &&
        actor.branchIds.includes(membership.enrolledBranchId),
    )
    .sort((left, right) => right.startedAt.localeCompare(left.startedAt));

  const recentMemberships = await Promise.all(
    visibleMemberships.slice(0, 8).map(async (membership) => {
      const membershipView = await getMembershipWithPlan(db, membership.id);
      const summary = await getMembershipSummary(db, membership.id);

      return {
        membershipId: membershipView.membershipId,
        branchId: membershipView.branchId,
        branchName:
          branchNameById.get(membershipView.branchId) ?? membershipView.branchId,
        customerName: membershipView.customerName,
        maskedPhone: maskPhoneNumber(membershipView.normalizedPhone),
        planName: membershipView.planName,
        thresholdCount: membershipView.thresholdCount,
        rewardCreditCount: membershipView.rewardCreditCount,
        status: membershipView.status,
        activePassId: membershipView.activePassId,
        activePassVersion: membershipView.activePassVersion,
        joinedAt: membership.startedAt,
        summary: {
          purchaseCount: summary.purchaseCount,
          rewardBalance: summary.rewardBalance,
          rewardRedeemedCount: summary.rewardRedeemedCount,
          lastActivityAt: summary.lastActivityAt,
        },
      };
    }),
  );

  if (actor.role === "CASHIER") {
    return {
      accessibleBranches,
      activeMembershipCount: visibleMemberships.length,
      recentMemberships,
      branchPerformance: [] as BranchPerformanceSnapshot[],
      teamMembers: [] as StaffPerformanceSnapshot[],
    };
  }

  const [staffRows, ledgerEvents] = await Promise.all([
    readQuery<StaffUserDoc>(
      db.collection(COLLECTIONS.staffUsers).where("tenantId", "==", actor.tenantId),
    ),
    readQuery<LedgerEventDoc>(
      db.collection(COLLECTIONS.ledgerEvents).where("tenantId", "==", actor.tenantId),
    ),
  ]);

  const visibleEvents = ledgerEvents.filter(
    (event) => !event.branchId || actor.branchIds.includes(event.branchId),
  );
  const teamMembers = buildStaffPerformanceSnapshots({
    actor,
    staffUsers: staffRows,
    branchNameById,
    events: visibleEvents,
  });
  const visibleStaffUsers =
    actor.role === "MERCHANT_ADMIN"
      ? staffRows
      : staffRows.filter((staff) => canViewStaffFromOperations(actor, staff));
  const branchPerformance = buildBranchPerformanceSnapshots({
    branches: accessibleBranches,
    memberships: visibleMemberships,
    staffUsers: visibleStaffUsers,
    events: visibleEvents,
  });

  return {
    accessibleBranches,
    activeMembershipCount: visibleMemberships.length,
    recentMemberships,
    branchPerformance,
    teamMembers,
  };
}

export async function addPurchase(
  membershipId: string,
  input: {
    branchId: string;
    quantity?: number;
    source: "QR_SCAN" | "PHONE_LOOKUP";
    idempotencyKey: string;
  },
  actor: StaffActor,
) {
  assertBranchAccess(actor, input.branchId);
  const db = requireDb();
  const membershipView = await getMembershipWithPlan(db, membershipId);
  assertTenantScope(actor, membershipView.tenantId);
  invariant(
    membershipView.status === "ACTIVE",
    "Only active memberships can earn purchases.",
    400,
    "MEMBERSHIP_INACTIVE",
  );

  if (
    membershipView.applicableBranchIds.length > 0 &&
    !membershipView.applicableBranchIds.includes(input.branchId)
  ) {
    throw new AppError(
      "This membership cannot earn at the selected branch.",
      400,
      "PLAN_BRANCH_MISMATCH",
    );
  }

  const requestPayload = {
    membershipId,
    branchId: input.branchId,
    quantity: input.quantity ?? 1,
    source: input.source,
  };

  const recentAdds = await readQuery<LedgerEventDoc>(
    db
      .collection(COLLECTIONS.ledgerEvents)
      .where("membershipId", "==", membershipId)
      .where("eventType", "==", "PURCHASE_ADDED")
      .where(
        "createdAt",
        ">=",
        nowIso(new Date(Date.now() - RAPID_PURCHASE_WINDOW_SECONDS * 1000)),
      )
      .orderBy("createdAt", "desc"),
  );

  const result = await runIdempotentOperation({
    tenantId: membershipView.tenantId,
    operation: "purchase-add",
    idempotencyKey: input.idempotencyKey,
    requestPayload,
    actor,
    run: async (tx, database) => {
      const membership = await getMembershipDoc(database, membershipId, tx);
      invariant(membership, "Membership not found.", 404, "MEMBERSHIP_NOT_FOUND");
      invariant(
        membership.status === "ACTIVE",
        "Only active memberships can earn purchases.",
        400,
        "MEMBERSHIP_INACTIVE",
      );

      const currentSummary = await getMembershipSummary(database, membershipId, tx);
      const quantity = input.quantity ?? 1;
      const membershipDetails = await getMembershipWithPlan(database, membershipId, tx);
      const nextPurchaseCount = currentSummary.purchaseCount + quantity;

      await insertLedgerEvent(database, tx, {
        tenantId: membership.tenantId,
        branchId: input.branchId,
        membershipId: membership.id,
        customerId: membership.customerId,
        eventType: "PURCHASE_ADDED",
        quantity,
        source: input.source,
        idempotencyKey: input.idempotencyKey,
        createdByStaffUserId: actor.staffUserId,
        metadata: {},
      });

      const unlockDelta = getUnlockDelta({
        previousPurchaseCount: currentSummary.purchaseCount,
        nextPurchaseCount,
        thresholdCount: membershipDetails.thresholdCount,
        rewardCreditCount: membershipDetails.rewardCreditCount,
      });

      for (
        let cycle = unlockDelta.previousCycle + 1;
        cycle <= unlockDelta.nextCycle;
        cycle += 1
      ) {
        await insertLedgerEvent(database, tx, {
          id: rewardUnlockEventId(membership.id, cycle),
          tenantId: membership.tenantId,
          branchId: input.branchId,
          membershipId: membership.id,
          customerId: membership.customerId,
          eventType: "REWARD_UNLOCKED",
          quantity: membershipDetails.rewardCreditCount,
          unlockCycle: cycle,
          source: "SYSTEM",
          createdByStaffUserId: actor.staffUserId,
          metadata: {
            cycle,
          },
        });
      }

      const updatedSummary: MembershipSummaryDoc = {
        ...currentSummary,
        purchaseCount: nextPurchaseCount,
        rewardEarnedCount:
          currentSummary.rewardEarnedCount + unlockDelta.rewardCreditsUnlocked,
        rewardBalance:
          currentSummary.rewardBalance + unlockDelta.rewardCreditsUnlocked,
        lastActivityAt: nowIso(),
        updatedAt: nowIso(),
        lineageMembershipIds: currentSummary.lineageMembershipIds,
      };
      invariant(
        updatedSummary.rewardBalance >= 0,
        "Summary rebuild encountered an invalid negative state.",
        500,
        "INVALID_SUMMARY_STATE",
      );

      tx.set(
        database.collection(COLLECTIONS.membershipSummaries).doc(membership.id),
        updatedSummary,
      );

      return {
        membershipId,
        purchaseCount: updatedSummary.purchaseCount,
        rewardBalance: updatedSummary.rewardBalance,
        unlockedRewardCredits: unlockDelta.rewardCreditsUnlocked,
      };
    },
  });

  if (!result.replayed && recentAdds.length > 0) {
    await recordSecurityEvent(db, {
      tenantId: membershipView.tenantId,
      branchId: input.branchId,
      staffUserId: actor.staffUserId,
      eventType: SECURITY_EVENT_TYPES.suspiciousActivity,
      scopeKey: `membership:${membershipId}`,
      metadata: {
        reason: "rapid_purchase_add_warning",
        recentAdds: recentAdds.length,
      },
    });
  }

  return result;
}

export async function consumeRedeemToken(
  input: {
    redeemToken: string;
    idempotencyKey: string;
  },
  actor: StaffActor,
  context: SecurityContext,
) {
  const db = requireDb();
  await assertRateLimit(db, "redeemConsume", {
    ...context,
    tenantId: actor.tenantId,
    staffUserId: actor.staffUserId,
    eventType: SECURITY_EVENT_TYPES.redeemConsumeAttempt,
    scopeKey: `staff:${actor.staffUserId}`,
    subjectKey: input.redeemToken.slice(-6),
  });

  const requestPayload = {
    redeemToken: input.redeemToken.slice(-6),
  };

  try {
    return await runIdempotentOperation({
      tenantId: actor.tenantId,
      operation: "redeem-consume",
      idempotencyKey: input.idempotencyKey,
      requestPayload,
      actor,
      run: async (tx, database) => {
        const tokenId = hashRedeemToken(input.redeemToken);
        const token = await getRedeemTokenDoc(database, tokenId, tx);
        invariant(token, "Redeem token is invalid.", 404, "REDEEM_TOKEN_INVALID");

        const disposition = getRedeemTokenDisposition({
          expiresAt: new Date(token.expiresAt),
          consumedAt: token.consumedAt ? new Date(token.consumedAt) : null,
          revokedAt: token.revokedAt ? new Date(token.revokedAt) : null,
        });
        if (disposition === "revoked") {
          throw new AppError("Redeem token was revoked.", 400, "REDEEM_TOKEN_REVOKED");
        }
        if (disposition === "used") {
          throw new AppError("Redeem token was already used.", 409, "REDEEM_TOKEN_USED");
        }
        if (disposition === "expired") {
          tx.set(
            database.collection(COLLECTIONS.redeemTokens).doc(token.id),
            {
              ...token,
              revokedAt: token.revokedAt ?? nowIso(),
              updatedAt: nowIso(),
            } satisfies RedeemTokenDoc,
          );
          throw new AppError("Redeem token has expired.", 410, "REDEEM_TOKEN_EXPIRED");
        }

        const membership = await getMembershipDoc(database, token.membershipId, tx);
        const pass = await getPassDoc(database, token.passId, tx);
        invariant(
          membership && pass,
          "Redeem token is invalid.",
          404,
          "REDEEM_TOKEN_INVALID",
        );

        assertTenantScope(actor, membership.tenantId);
        assertBranchAccess(actor, membership.enrolledBranchId);
        invariant(
          membership.status === "ACTIVE",
          "Only active memberships can redeem rewards.",
          400,
          "MEMBERSHIP_INACTIVE",
        );
        invariant(
          pass.status === "ACTIVE" &&
            membership.activePassId === pass.id &&
            membership.activePassVersion === pass.passVersion,
          "The originating pass is no longer active.",
          409,
          "PASS_REVOKED",
        );

        const summary = await getMembershipSummary(database, membership.id, tx);
        invariant(
          summary.rewardBalance > 0,
          "No reward balance is available.",
          409,
          "NO_REWARD_BALANCE",
        );

        await insertLedgerEvent(database, tx, {
          tenantId: membership.tenantId,
          branchId: membership.enrolledBranchId,
          membershipId: membership.id,
          customerId: membership.customerId,
          eventType: "REWARD_REDEEMED",
          quantity: 1,
          source: "REDEEM_TOKEN",
          idempotencyKey: input.idempotencyKey,
          createdByStaffUserId: actor.staffUserId,
          metadata: {
            tokenPreview: token.tokenPreview,
          },
        });

        tx.set(
          database.collection(COLLECTIONS.redeemTokens).doc(token.id),
          {
            ...token,
            consumedAt: nowIso(),
            updatedAt: nowIso(),
          } satisfies RedeemTokenDoc,
        );
        tx.set(
          database.collection(COLLECTIONS.memberPasses).doc(pass.id),
          {
            ...pass,
            currentRedeemTokenId:
              pass.currentRedeemTokenId === token.id ? null : pass.currentRedeemTokenId,
            updatedAt: nowIso(),
          } satisfies MemberPassDoc,
        );
        tx.set(
          database.collection(COLLECTIONS.memberships).doc(membership.id),
          {
            ...membership,
            currentRedeemTokenId:
              membership.currentRedeemTokenId === token.id
                ? null
                : membership.currentRedeemTokenId,
            currentRedeemTokenPassId:
              membership.currentRedeemTokenId === token.id
                ? null
                : membership.currentRedeemTokenPassId,
            updatedAt: nowIso(),
          } satisfies MembershipDoc,
        );

        const updatedSummary: MembershipSummaryDoc = {
          ...summary,
          rewardRedeemedCount: summary.rewardRedeemedCount + 1,
          rewardBalance: summary.rewardBalance - 1,
          lastActivityAt: nowIso(),
          updatedAt: nowIso(),
        };
        invariant(
          updatedSummary.rewardBalance >= 0,
          "Summary rebuild encountered an invalid negative state.",
          500,
          "INVALID_SUMMARY_STATE",
        );
        tx.set(
          database.collection(COLLECTIONS.membershipSummaries).doc(membership.id),
          updatedSummary,
        );

        return {
          membershipId: membership.id,
          rewardBalance: updatedSummary.rewardBalance,
        };
      },
    });
  } catch (error) {
    if (
      error instanceof AppError &&
      [
        "REDEEM_TOKEN_INVALID",
        "REDEEM_TOKEN_REVOKED",
        "REDEEM_TOKEN_USED",
        "REDEEM_TOKEN_EXPIRED",
        "PASS_REVOKED",
      ].includes(error.code)
    ) {
      await recordSecurityEvent(db, {
        ...context,
        tenantId: actor.tenantId,
        staffUserId: actor.staffUserId,
        eventType: SECURITY_EVENT_TYPES.suspiciousActivity,
        scopeKey: `staff:${actor.staffUserId}`,
        subjectKey: input.redeemToken.slice(-6),
        metadata: {
          reason: "redeem_consume_rejected",
          code: error.code,
        },
      });
    }
    throw error;
  }
}

export async function redeemByRecovery(
  membershipId: string,
  input: {
    reasonCode: string;
    verificationNote: string;
    idempotencyKey: string;
  },
  actor: StaffActor,
) {
  if (actor.role === "CASHIER") {
    throw new AppError(
      "Counter staff cannot use phone-based redemption recovery.",
      403,
      "RECOVERY_REQUIRES_MANAGER",
    );
  }

  const db = requireDb();
  const membership = await getMembershipWithPlan(db, membershipId);
  assertTenantScope(actor, membership.tenantId);
  assertBranchAccess(actor, membership.branchId);

  const result = await runIdempotentOperation({
    tenantId: membership.tenantId,
    operation: "redeem-recovery",
    idempotencyKey: input.idempotencyKey,
    requestPayload: {
      membershipId,
      reasonCode: input.reasonCode,
      verificationNote: input.verificationNote,
    },
    actor,
    run: async (tx, database) => {
      const membershipDoc = await getMembershipDoc(database, membershipId, tx);
      invariant(membershipDoc, "Membership not found.", 404, "MEMBERSHIP_NOT_FOUND");
      invariant(
        membershipDoc.status === "ACTIVE",
        "Only active memberships can redeem rewards.",
        400,
        "MEMBERSHIP_INACTIVE",
      );

      const summary = await getMembershipSummary(database, membershipId, tx);
      invariant(
        summary.rewardBalance > 0,
        "No reward balance is available.",
        409,
        "NO_REWARD_BALANCE",
      );

      await insertLedgerEvent(database, tx, {
        tenantId: membershipDoc.tenantId,
        branchId: membershipDoc.enrolledBranchId,
        membershipId: membershipDoc.id,
        customerId: membershipDoc.customerId,
        eventType: "REWARD_REDEEMED",
        quantity: 1,
        source: "MANAGER_RECOVERY",
        idempotencyKey: input.idempotencyKey,
        reasonCode: input.reasonCode,
        createdByStaffUserId: actor.staffUserId,
        metadata: {
          verificationNote: input.verificationNote,
          recovery: true,
        },
      });

      const updatedSummary: MembershipSummaryDoc = {
        ...summary,
        rewardRedeemedCount: summary.rewardRedeemedCount + 1,
        rewardBalance: summary.rewardBalance - 1,
        lastActivityAt: nowIso(),
        updatedAt: nowIso(),
      };
      invariant(
        updatedSummary.rewardBalance >= 0,
        "Summary rebuild encountered an invalid negative state.",
        500,
        "INVALID_SUMMARY_STATE",
      );
      tx.set(
        database.collection(COLLECTIONS.membershipSummaries).doc(membershipDoc.id),
        updatedSummary,
      );

      return {
        membershipId,
        rewardBalance: updatedSummary.rewardBalance,
      };
    },
  });

  if (!result.replayed) {
    await recordSecurityEvent(db, {
      tenantId: membership.tenantId,
      branchId: membership.branchId,
      staffUserId: actor.staffUserId,
      eventType: SECURITY_EVENT_TYPES.suspiciousActivity,
      scopeKey: `membership:${membershipId}`,
      metadata: {
        reason: "manager_recovery_redemption",
        reasonCode: input.reasonCode,
      },
    });
  }

  return result;
}

export async function reissuePass(
  membershipId: string,
  input: { reasonCode: string; idempotencyKey: string },
  actor: StaffActor,
) {
  if (actor.role === "CASHIER") {
    throw new AppError("Only store managers can reissue passes.", 403, "FORBIDDEN");
  }

  const db = requireDb();
  const membership = await getMembershipWithPlan(db, membershipId);
  assertTenantScope(actor, membership.tenantId);
  assertBranchAccess(actor, membership.branchId);

  const result = await runIdempotentOperation({
    tenantId: membership.tenantId,
    operation: "pass-reissue",
    idempotencyKey: input.idempotencyKey,
    requestPayload: { membershipId, reasonCode: input.reasonCode },
    actor,
    run: async (tx, database) => {
      const membershipDoc = await getMembershipDoc(database, membershipId, tx);
      invariant(membershipDoc, "Membership not found.", 404, "MEMBERSHIP_NOT_FOUND");

      const activePass = await getActivePassForMembership(database, membershipId, tx);
      await revokePass(database, tx, activePass);

      tx.set(
        database.collection(COLLECTIONS.memberships).doc(membershipDoc.id),
        {
          ...membershipDoc,
          activePassId: null,
          activePassVersion: null,
          currentRedeemTokenId: null,
          currentRedeemTokenPassId: null,
          updatedAt: nowIso(),
        } satisfies MembershipDoc,
      );

      const issued = await createPass(
        database,
        tx,
        {
          ...membershipDoc,
          activePassId: null,
          activePassVersion: null,
          currentRedeemTokenId: null,
          currentRedeemTokenPassId: null,
          updatedAt: nowIso(),
        },
        actor,
        "PASS_REISSUED",
      );

      return {
        membershipId,
        passUrl: issued.passUrl,
      };
    },
  });

  if (!result.replayed) {
    await recordSecurityEvent(db, {
      tenantId: membership.tenantId,
      branchId: membership.branchId,
      staffUserId: actor.staffUserId,
      eventType: SECURITY_EVENT_TYPES.suspiciousActivity,
      scopeKey: `membership:${membershipId}`,
      metadata: {
        reason: "pass_reissued",
        reasonCode: input.reasonCode,
      },
    });
  }

  return result;
}

async function reverseLedgerEvent(
  membershipId: string,
  eventType: "PURCHASE_REVERSED" | "REWARD_REDEMPTION_REVERSED",
  sourceEventType: "PURCHASE_ADDED" | "REWARD_REDEEMED",
  input: { reasonCode: string; idempotencyKey: string },
  actor: StaffActor,
) {
  if (actor.role === "CASHIER") {
    throw new AppError("Only store managers can reverse events.", 403, "FORBIDDEN");
  }

  const db = requireDb();
  const membership = await getMembershipWithPlan(db, membershipId);
  assertTenantScope(actor, membership.tenantId);
  assertBranchAccess(actor, membership.branchId);

  return runIdempotentOperation({
    tenantId: membership.tenantId,
    operation: eventType,
    idempotencyKey: input.idempotencyKey,
    requestPayload: { membershipId, reasonCode: input.reasonCode },
    actor,
    run: async (tx, database) => {
      const membershipDoc = await getMembershipDoc(database, membershipId, tx);
      invariant(membershipDoc, "Membership not found.", 404, "MEMBERSHIP_NOT_FOUND");
      invariant(
        membershipDoc.status === "ACTIVE",
        "There is no event to reverse.",
        409,
        "REVERSAL_SOURCE_MISSING",
      );

      const sourceEvent = await getLatestUnreversedSourceEvent(
        database,
        membershipDoc,
        sourceEventType,
        eventType,
        tx,
      );
      invariant(
        sourceEvent,
        "There is no event to reverse.",
        409,
        "REVERSAL_SOURCE_MISSING",
      );

      await insertLedgerEvent(database, tx, {
        tenantId: membershipDoc.tenantId,
        branchId: membershipDoc.enrolledBranchId,
        membershipId: membershipDoc.id,
        customerId: membershipDoc.customerId,
        eventType,
        quantity: sourceEvent.quantity,
        reasonCode: input.reasonCode,
        source: "MANAGER_REVIEW",
        idempotencyKey: input.idempotencyKey,
        createdByStaffUserId: actor.staffUserId,
        metadata: {
          reversedEventId: sourceEvent.id,
        },
      });

      const rebuilt = await rebuildAndPersistSummary(database, membershipDoc.id, tx);
      return {
        membershipId,
        rewardBalance: rebuilt.rewardBalance,
        purchaseCount: rebuilt.purchaseCount,
      };
    },
  });
}

export function reversePurchase(
  membershipId: string,
  input: { reasonCode: string; idempotencyKey: string },
  actor: StaffActor,
) {
  return reverseLedgerEvent(
    membershipId,
    "PURCHASE_REVERSED",
    "PURCHASE_ADDED",
    input,
    actor,
  );
}

export function reverseRedemption(
  membershipId: string,
  input: { reasonCode: string; idempotencyKey: string },
  actor: StaffActor,
) {
  return reverseLedgerEvent(
    membershipId,
    "REWARD_REDEMPTION_REVERSED",
    "REWARD_REDEEMED",
    input,
    actor,
  );
}

export async function mergeMemberships(
  input: {
    survivorMembershipId: string;
    obsoleteMembershipId: string;
    reasonCode: string;
    idempotencyKey: string;
  },
  actor: StaffActor,
) {
  if (actor.role === "CASHIER") {
    throw new AppError("Only store managers can merge memberships.", 403, "FORBIDDEN");
  }

  invariant(
    input.survivorMembershipId !== input.obsoleteMembershipId,
    "Memberships must be different.",
    400,
    "MERGE_INVALID",
  );

  const db = requireDb();
  const survivor = await getMembershipWithPlan(db, input.survivorMembershipId);
  const obsolete = await getMembershipWithPlan(db, input.obsoleteMembershipId);
  assertTenantScope(actor, survivor.tenantId);
  assertBranchAccess(actor, survivor.branchId);
  assertBranchAccess(actor, obsolete.branchId);
  invariant(
    survivor.tenantId === obsolete.tenantId,
    "Cross-tenant merges are not allowed.",
    400,
    "MERGE_TENANT_MISMATCH",
  );

  return runIdempotentOperation({
    tenantId: survivor.tenantId,
    operation: "membership-merge",
    idempotencyKey: input.idempotencyKey,
    requestPayload: input,
    actor,
    run: async (tx, database) => {
      const survivorMembership = await getMembershipDoc(
        database,
        input.survivorMembershipId,
        tx,
      );
      const obsoleteMembership = await getMembershipDoc(
        database,
        input.obsoleteMembershipId,
        tx,
      );
      invariant(
        survivorMembership && obsoleteMembership,
        "Membership not found.",
        404,
        "MEMBERSHIP_NOT_FOUND",
      );
      invariant(
        survivorMembership.tenantId === obsoleteMembership.tenantId,
        "Cross-tenant merges are not allowed.",
        400,
        "MERGE_TENANT_MISMATCH",
      );

      const obsoletePasses = await readQuery<MemberPassDoc>(
        database
          .collection(COLLECTIONS.memberPasses)
          .where("membershipId", "==", obsoleteMembership.id),
        tx,
      );

      for (const pass of obsoletePasses) {
        if (pass.status === "ACTIVE") {
          await revokePass(database, tx, pass);
        } else {
          tx.set(
            database.collection(COLLECTIONS.memberPasses).doc(pass.id),
            {
              ...pass,
              currentRedeemTokenId: null,
              updatedAt: nowIso(),
            } satisfies MemberPassDoc,
          );
        }
      }

      const mergedIds = dedupeIds([
        ...(survivorMembership.mergedMembershipIds ?? []),
        obsoleteMembership.id,
        ...(obsoleteMembership.mergedMembershipIds ?? []),
      ]);

      tx.set(
        database.collection(COLLECTIONS.memberships).doc(survivorMembership.id),
        {
          ...survivorMembership,
          mergedMembershipIds: mergedIds,
          updatedAt: nowIso(),
        } satisfies MembershipDoc,
      );
      tx.set(
        database.collection(COLLECTIONS.memberships).doc(obsoleteMembership.id),
        {
          ...obsoleteMembership,
          status: "MERGED",
          mergedIntoMembershipId: survivorMembership.id,
          canonicalMembershipId: survivorMembership.id,
          activePassId: null,
          activePassVersion: null,
          currentRedeemTokenId: null,
          currentRedeemTokenPassId: null,
          updatedAt: nowIso(),
        } satisfies MembershipDoc,
      );

      const obsoleteLookupRef = database
        .collection(COLLECTIONS.activeMembershipLookups)
        .doc(
          activeMembershipLookupId(
            obsoleteMembership.tenantId,
            obsoleteMembership.customerId,
            obsoleteMembership.planId,
          ),
        );
      tx.delete(obsoleteLookupRef);

      const mergeDoc: MembershipMergeDoc = {
        id: membershipMergeId(survivorMembership.id, obsoleteMembership.id),
        tenantId: survivorMembership.tenantId,
        survivorMembershipId: survivorMembership.id,
        obsoleteMembershipId: obsoleteMembership.id,
        reasonCode: input.reasonCode,
        actorStaffUserId: actor.staffUserId,
        createdAt: nowIso(),
      };
      tx.set(
        database.collection(COLLECTIONS.membershipMerges).doc(mergeDoc.id),
        mergeDoc,
      );

      await insertLedgerEvent(database, tx, {
        tenantId: survivorMembership.tenantId,
        branchId: survivorMembership.enrolledBranchId,
        membershipId: survivorMembership.id,
        customerId: survivorMembership.customerId,
        eventType: "ACCOUNT_MERGED",
        quantity: 1,
        reasonCode: input.reasonCode,
        source: "MANAGER_REVIEW",
        idempotencyKey: input.idempotencyKey,
        createdByStaffUserId: actor.staffUserId,
        metadata: {
          obsoleteMembershipId: obsoleteMembership.id,
        },
      });

      let activePass = await getActivePassForMembership(
        database,
        survivorMembership.id,
        tx,
      );
      if (!activePass) {
        const issued = await createPass(
          database,
          tx,
          {
            ...survivorMembership,
            mergedMembershipIds: mergedIds,
            updatedAt: nowIso(),
          },
          actor,
          "PASS_REISSUED",
        );
        activePass = issued.pass;
      }

      await rebuildAndPersistSummary(database, survivorMembership.id, tx);

      return {
        survivorMembershipId: survivorMembership.id,
        obsoleteMembershipId: obsoleteMembership.id,
        activePassId: activePass.id,
      };
    },
  });
}

export async function createPlan(
  input: {
    tenantId: string;
    name: string;
    eligibleLabel: string;
    thresholdCount: number;
    rewardCreditCount: number;
    applicableBranchIds?: string[];
  },
  actor: StaffActor,
) {
  if (actor.role !== "MERCHANT_ADMIN") {
    throw new AppError("Only business admins can create plans.", 403, "FORBIDDEN");
  }

  assertTenantScope(actor, input.tenantId);
  const db = requireDb();
  await ensureBranchIdsBelongToTenant(
    db,
    input.tenantId,
    input.applicableBranchIds ?? [],
  );

  return withTenantTransaction(
    {
      tenantId: input.tenantId,
      actorId: actor.staffUserId,
      actorRole: actor.role,
    },
    async (tx, database) => {
      const createdAt = nowIso();
      const planVersion: PlanVersionDoc = {
        id: randomUUID(),
        tenantId: input.tenantId,
        planId: randomUUID(),
        versionNumber: 1,
        name: input.name,
        eligibleLabel: input.eligibleLabel,
        thresholdCount: input.thresholdCount,
        rewardCreditCount: input.rewardCreditCount,
        snapshot: {
          applicableBranchIds: input.applicableBranchIds ?? [],
          status: "ACTIVE",
        },
        createdAt,
      };
      const plan: PlanDoc = {
        id: planVersion.planId,
        tenantId: input.tenantId,
        name: input.name,
        eligibleLabel: input.eligibleLabel,
        thresholdCount: input.thresholdCount,
        rewardCreditCount: input.rewardCreditCount,
        currentVersionNumber: 1,
        currentVersionId: planVersion.id,
        applicableBranchIds: input.applicableBranchIds ?? [],
        status: "ACTIVE",
        validityStartsAt: null,
        validityEndsAt: null,
        redemptionConstraints: {},
        createdAt,
        updatedAt: createdAt,
      };

      tx.set(database.collection(COLLECTIONS.plans).doc(plan.id), plan);
      tx.set(
        database.collection(COLLECTIONS.planVersions).doc(planVersion.id),
        planVersion,
      );

      return plan;
    },
  );
}

export async function updatePlan(
  planId: string,
  input: {
    name?: string;
    eligibleLabel?: string;
    thresholdCount?: number;
    rewardCreditCount?: number;
    status?: "ACTIVE" | "INACTIVE";
    applicableBranchIds?: string[];
  },
  actor: StaffActor,
) {
  if (actor.role !== "MERCHANT_ADMIN") {
    throw new AppError("Only business admins can update plans.", 403, "FORBIDDEN");
  }

  const db = requireDb();
  const existing = await readDoc<PlanDoc>(db.collection(COLLECTIONS.plans).doc(planId));
  invariant(existing, "Plan not found.", 404, "PLAN_NOT_FOUND");
  assertTenantScope(actor, existing.tenantId);
  await ensureBranchIdsBelongToTenant(
    db,
    existing.tenantId,
    input.applicableBranchIds ?? existing.applicableBranchIds,
  );

  return withTenantTransaction(
    {
      tenantId: existing.tenantId,
      actorId: actor.staffUserId,
      actorRole: actor.role,
    },
    async (tx, database) => {
      const versionNumber = existing.currentVersionNumber + 1;
      const updated: PlanDoc = {
        ...existing,
        name: input.name ?? existing.name,
        eligibleLabel: input.eligibleLabel ?? existing.eligibleLabel,
        thresholdCount: input.thresholdCount ?? existing.thresholdCount,
        rewardCreditCount:
          input.rewardCreditCount ?? existing.rewardCreditCount,
        status: input.status ?? existing.status,
        applicableBranchIds:
          input.applicableBranchIds ?? existing.applicableBranchIds,
        currentVersionNumber: versionNumber,
        currentVersionId: randomUUID(),
        updatedAt: nowIso(),
      };
      const version: PlanVersionDoc = {
        id: updated.currentVersionId,
        tenantId: existing.tenantId,
        planId,
        versionNumber,
        name: updated.name,
        eligibleLabel: updated.eligibleLabel,
        thresholdCount: updated.thresholdCount,
        rewardCreditCount: updated.rewardCreditCount,
        snapshot: {
          applicableBranchIds: updated.applicableBranchIds,
          status: updated.status,
        },
        createdAt: nowIso(),
      };

      tx.set(database.collection(COLLECTIONS.plans).doc(planId), updated);
      tx.set(database.collection(COLLECTIONS.planVersions).doc(version.id), version);

      return updated;
    },
  );
}

export async function createBranch(
  input: {
    tenantId: string;
    code: string;
    name: string;
    timezone?: string;
    address?: string;
  },
  actor: StaffActor,
) {
  if (actor.role !== "MERCHANT_ADMIN") {
    throw new AppError("Only business admins can create branches.", 403, "FORBIDDEN");
  }

  assertTenantScope(actor, input.tenantId);
  const db = requireDb();
  const lookupRef = db.collection(COLLECTIONS.branchCodeLookups).doc(input.code);

  return withTenantTransaction(
    {
      tenantId: input.tenantId,
      actorId: actor.staffUserId,
      actorRole: actor.role,
    },
    async (tx, database) => {
      const existingLookup = await readDoc<BranchCodeLookupDoc>(lookupRef, tx);
      invariant(
        !existingLookup,
        "Branch code is already assigned.",
        409,
        "BRANCH_CODE_CONFLICT",
      );

      const branch: BranchDoc = {
        id: randomUUID(),
        tenantId: input.tenantId,
        code: input.code,
        name: input.name,
        timezone: input.timezone ?? "UTC",
        address: input.address ?? null,
        status: "ACTIVE",
        createdAt: nowIso(),
      };

      tx.set(database.collection(COLLECTIONS.branches).doc(branch.id), branch);
      tx.set(
        lookupRef,
        {
          id: lookupRef.id,
          branchId: branch.id,
          tenantId: branch.tenantId,
          status: branch.status,
          updatedAt: nowIso(),
        } satisfies BranchCodeLookupDoc,
      );

      return branch;
    },
  );
}

export async function updateBranch(
  branchId: string,
  input: {
    name?: string;
    timezone?: string;
    address?: string | null;
    status?: "ACTIVE" | "INACTIVE";
  },
  actor: StaffActor,
) {
  if (actor.role !== "MERCHANT_ADMIN") {
    throw new AppError("Only business admins can update branches.", 403, "FORBIDDEN");
  }

  const db = requireDb();
  const existing = await readDoc<BranchDoc>(db.collection(COLLECTIONS.branches).doc(branchId));
  invariant(existing, "Branch not found.", 404, "BRANCH_NOT_FOUND");
  assertTenantScope(actor, existing.tenantId);

  return withTenantTransaction(
    {
      tenantId: existing.tenantId,
      actorId: actor.staffUserId,
      actorRole: actor.role,
    },
    async (tx, database) => {
      const current = await readDoc<BranchDoc>(
        database.collection(COLLECTIONS.branches).doc(branchId),
        tx,
      );
      invariant(current, "Branch not found.", 404, "BRANCH_NOT_FOUND");

      const updated: BranchDoc = {
        ...current,
        name: input.name ?? current.name,
        timezone: input.timezone ?? current.timezone,
        address: input.address === undefined ? current.address : input.address,
        status: input.status ?? current.status,
      };
      const updatedAt = nowIso();

      tx.set(database.collection(COLLECTIONS.branches).doc(branchId), updated);
      tx.set(
        database.collection(COLLECTIONS.branchCodeLookups).doc(current.code),
        {
          id: current.code,
          branchId: current.id,
          tenantId: current.tenantId,
          status: updated.status,
          updatedAt,
        } satisfies BranchCodeLookupDoc,
      );

      return updated;
    },
  );
}

export async function createStaffUser(
  input: {
    tenantId: string;
    fullName: string;
    email: string;
    role: StaffActor["role"];
    primaryBranchId?: string | null;
    authUserId?: string;
    branchIds?: string[];
  },
  actor: StaffActor,
) {
  if (actor.role !== "MERCHANT_ADMIN") {
    throw new AppError("Only business admins can invite staff.", 403, "FORBIDDEN");
  }

  assertTenantScope(actor, input.tenantId);
  const branchIds = dedupeIds(input.branchIds ?? []);
  const requiresBranchAssignment = input.role !== "MERCHANT_ADMIN";
  invariant(
    !requiresBranchAssignment || (input.primaryBranchId && branchIds.includes(input.primaryBranchId)),
    "Primary branch must be part of branch assignments.",
    400,
    "PRIMARY_BRANCH_REQUIRED",
  );
  invariant(
    requiresBranchAssignment || branchIds.length === 0 || input.primaryBranchId === null || input.primaryBranchId === undefined || branchIds.includes(input.primaryBranchId),
    "Primary branch must be part of branch assignments.",
    400,
    "PRIMARY_BRANCH_REQUIRED",
  );

  const db = requireDb();
  if (branchIds.length > 0) {
    await ensureBranchIdsBelongToTenant(db, input.tenantId, branchIds);
  }
  await assertStaffEmailAvailable(db, normalizeEmailAddress(input.email));

  return withTenantTransaction(
    {
      tenantId: input.tenantId,
      actorId: actor.staffUserId,
      actorRole: actor.role,
    },
    async (tx, database) => {
      const createdAt = nowIso();
      await assertStaffEmailAvailable(database, normalizeEmailAddress(input.email), tx);
      const staff: StaffUserDoc = {
        id: randomUUID(),
        tenantId: input.tenantId,
        fullName: input.fullName,
        email: input.email,
        emailNormalized: normalizeEmailAddress(input.email),
        role: input.role,
        primaryBranchId: input.primaryBranchId ?? null,
        authUserId: input.authUserId ?? null,
        status: "INVITED",
        branchIds,
        createdAt,
      };

      tx.set(database.collection(COLLECTIONS.staffUsers).doc(staff.id), staff);
      for (const branchId of branchIds) {
        const assignment: StaffBranchAssignmentDoc = {
          id: staffBranchAssignmentId(staff.id, branchId),
          tenantId: input.tenantId,
          staffUserId: staff.id,
          branchId,
          isPrimary: branchId === input.primaryBranchId,
          createdAt,
        };
        tx.set(
          database
            .collection(COLLECTIONS.staffBranchAssignments)
            .doc(assignment.id),
          assignment,
        );
      }

      return staff;
    },
  );
}

export async function updateStaffUserStatus(
  staffUserId: string,
  input: {
    status: "ACTIVE" | "DISABLED";
  },
  actor: StaffActor,
) {
  if (actor.role === "CASHIER") {
    throw new AppError("Counter staff cannot manage other staff accounts.", 403, "FORBIDDEN");
  }

  const db = requireDb();
  const existing = await readDoc<StaffUserDoc>(db.collection(COLLECTIONS.staffUsers).doc(staffUserId));
  invariant(existing, "Staff account not found.", 404, "STAFF_NOT_FOUND");
  assertTenantScope(actor, existing.tenantId);
  invariant(
    canManageStaffStatus(actor, existing),
    actor.role === "MANAGER"
      ? "Store managers can manage counter staff in their assigned branches only."
      : "Business admins can manage counter staff and store managers only.",
    403,
    "FORBIDDEN",
  );

  return withTenantTransaction(
    {
      tenantId: existing.tenantId,
      actorId: actor.staffUserId,
      actorRole: actor.role,
    },
    async (tx, database) => {
      const current = await readDoc<StaffUserDoc>(
        database.collection(COLLECTIONS.staffUsers).doc(staffUserId),
        tx,
      );
      invariant(current, "Staff account not found.", 404, "STAFF_NOT_FOUND");
      assertTenantScope(actor, current.tenantId);
      invariant(
        canManageStaffStatus(actor, current),
        actor.role === "MANAGER"
          ? "Store managers can manage counter staff in their assigned branches only."
          : "Business admins can manage counter staff and store managers only.",
        403,
        "FORBIDDEN",
      );

      const updated: StaffUserDoc = {
        ...current,
        status: input.status,
      };
      tx.set(database.collection(COLLECTIONS.staffUsers).doc(current.id), updated);
      return updated;
    },
  );
}

export async function updateStaffUser(
  staffUserId: string,
  input: {
    fullName?: string;
    email?: string;
    role?: StaffActor["role"];
    primaryBranchId?: string | null;
    branchIds?: string[];
    status?: StaffStatus;
  },
  actor: StaffActor,
) {
  if (actor.role !== "MERCHANT_ADMIN") {
    throw new AppError("Only business admins can edit staff records.", 403, "FORBIDDEN");
  }

  const db = requireDb();
  const existing = await readDoc<StaffUserDoc>(db.collection(COLLECTIONS.staffUsers).doc(staffUserId));
  invariant(existing, "Staff account not found.", 404, "STAFF_NOT_FOUND");
  assertTenantScope(actor, existing.tenantId);

  if (existing.id === actor.staffUserId && input.role && input.role !== existing.role) {
    throw new AppError("You cannot change your own role.", 400, "SELF_UPDATE_FORBIDDEN");
  }

  if (existing.id === actor.staffUserId && input.status === "DISABLED") {
    throw new AppError("You cannot disable your own account.", 400, "SELF_UPDATE_FORBIDDEN");
  }

  const nextEmail = (input.email ?? existing.email).trim();
  const nextEmailNormalized = normalizeEmailAddress(nextEmail);
  if (nextEmailNormalized !== existing.emailNormalized) {
    await assertStaffEmailAvailable(db, nextEmailNormalized, undefined, existing.id);
  }

  let nextRole = input.role ?? existing.role;
  let nextBranchIds = dedupeIds(input.branchIds ?? existing.branchIds ?? []);
  if (nextRole === "MERCHANT_ADMIN" && nextBranchIds.length === 0) {
    nextBranchIds = await listTenantBranchIds(db, existing.tenantId);
  }
  if (nextBranchIds.length > 0) {
    await ensureBranchIdsBelongToTenant(db, existing.tenantId, nextBranchIds);
  }

  const nextPrimaryBranchId =
    input.primaryBranchId !== undefined ? input.primaryBranchId : existing.primaryBranchId;
  const requiresBranchAssignment = nextRole !== "MERCHANT_ADMIN";
  invariant(
    !requiresBranchAssignment || (nextPrimaryBranchId && nextBranchIds.includes(nextPrimaryBranchId)),
    "Primary branch must be part of branch assignments.",
    400,
    "PRIMARY_BRANCH_REQUIRED",
  );
  invariant(
    requiresBranchAssignment ||
      nextPrimaryBranchId === null ||
      nextPrimaryBranchId === undefined ||
      nextBranchIds.includes(nextPrimaryBranchId),
    "Primary branch must be part of branch assignments.",
    400,
    "PRIMARY_BRANCH_REQUIRED",
  );

  return withTenantTransaction(
    {
      tenantId: existing.tenantId,
      actorId: actor.staffUserId,
      actorRole: actor.role,
    },
    async (tx, database) => {
      const current = await readDoc<StaffUserDoc>(
        database.collection(COLLECTIONS.staffUsers).doc(staffUserId),
        tx,
      );
      invariant(current, "Staff account not found.", 404, "STAFF_NOT_FOUND");
      assertTenantScope(actor, current.tenantId);

      const updatedRole = input.role ?? current.role;
      let updatedBranchIds = dedupeIds(input.branchIds ?? current.branchIds ?? []);
      if (updatedRole === "MERCHANT_ADMIN" && updatedBranchIds.length === 0) {
        updatedBranchIds = await listTenantBranchIds(database, current.tenantId, tx);
      }
      if (updatedBranchIds.length > 0) {
        await ensureBranchIdsBelongToTenant(database, current.tenantId, updatedBranchIds, tx);
      }

      const updatedPrimaryBranchId =
        input.primaryBranchId !== undefined ? input.primaryBranchId : current.primaryBranchId;
      const updatedRequiresBranchAssignment = updatedRole !== "MERCHANT_ADMIN";
      invariant(
        !updatedRequiresBranchAssignment ||
          (updatedPrimaryBranchId && updatedBranchIds.includes(updatedPrimaryBranchId)),
        "Primary branch must be part of branch assignments.",
        400,
        "PRIMARY_BRANCH_REQUIRED",
      );
      invariant(
        updatedRequiresBranchAssignment ||
          updatedPrimaryBranchId === null ||
          updatedPrimaryBranchId === undefined ||
          updatedBranchIds.includes(updatedPrimaryBranchId),
        "Primary branch must be part of branch assignments.",
        400,
        "PRIMARY_BRANCH_REQUIRED",
      );

      if (nextEmailNormalized !== current.emailNormalized) {
        await assertStaffEmailAvailable(database, nextEmailNormalized, tx, current.id);
      }

      const updated: StaffUserDoc = {
        ...current,
        fullName: input.fullName?.trim() ?? current.fullName,
        email: nextEmail,
        emailNormalized: nextEmailNormalized,
        role: updatedRole,
        primaryBranchId: updatedPrimaryBranchId ?? null,
        branchIds: updatedBranchIds,
        status: input.status ?? current.status,
      };

      tx.set(database.collection(COLLECTIONS.staffUsers).doc(current.id), updated);
      await syncStaffBranchAssignments({
        database,
        tx,
        tenantId: current.tenantId,
        staffUserId: current.id,
        branchIds: updated.branchIds,
        primaryBranchId: updated.primaryBranchId,
      });

      return updated;
    },
  );
}

export async function createTenant(
  input: {
    name: string;
    slug?: string;
  },
) {
  const slug = slugifyTenant(input.slug || input.name);
  invariant(slug.length >= 2, "Tenant slug must be at least 2 characters.", 400, "TENANT_SLUG_INVALID");

  const db = requireDb();
  const tenantId = `tenant:${slug}`;

  return db.runTransaction(async (tx) => {
    const tenantRef = db.collection(COLLECTIONS.tenants).doc(tenantId);
    const existing = await tx.get(tenantRef);
    invariant(!existing.exists, "Tenant slug is already in use.", 409, "TENANT_SLUG_CONFLICT");

    const tenant: TenantDoc = {
      id: tenantId,
      slug,
      name: input.name.trim(),
      status: "ACTIVE",
      branding: {},
      createdAt: nowIso(),
    };

    tx.set(tenantRef, tenant);
    return tenant;
  });
}

export async function updateTenant(
  tenantId: string,
  input: {
    name?: string;
    status?: TenantDoc["status"];
  },
) {
  const db = requireDb();

  return db.runTransaction(async (tx) => {
    const tenantRef = db.collection(COLLECTIONS.tenants).doc(tenantId);
    const snapshot = await tx.get(tenantRef);
    invariant(snapshot.exists, "Tenant not found.", 404, "TENANT_NOT_FOUND");

    const current = snapshot.data() as TenantDoc;
    const updated: TenantDoc = {
      ...current,
      name: input.name?.trim() ?? current.name,
      status: input.status ?? current.status,
    };

    tx.set(tenantRef, updated);
    return updated;
  });
}

export async function createPlatformAdminUser(
  input: {
    fullName: string;
    email: string;
    authUserId?: string;
  },
) {
  const db = requireDb();
  const emailNormalized = normalizeEmailAddress(input.email);
  await assertPlatformAdminEmailAvailable(db, emailNormalized);

  const platformAdmin: PlatformAdminUserDoc = {
    id: randomUUID(),
    authUserId: input.authUserId ?? null,
    status: input.authUserId ? "ACTIVE" : "INVITED",
    fullName: input.fullName.trim(),
    email: input.email.trim(),
    emailNormalized,
    createdAt: nowIso(),
  };

  await db.collection(COLLECTIONS.platformAdminUsers).doc(platformAdmin.id).set(platformAdmin);
  return platformAdmin;
}

export async function updatePlatformAdminUser(
  platformAdminUserId: string,
  input: {
    fullName?: string;
    email?: string;
    status?: StaffStatus;
  },
  actor: PlatformActor,
) {
  const db = requireDb();
  const existing = await readDoc<PlatformAdminUserDoc>(
    db.collection(COLLECTIONS.platformAdminUsers).doc(platformAdminUserId),
  );
  invariant(existing, "Platform admin account not found.", 404, "PLATFORM_ADMIN_NOT_FOUND");

  if (
    existing.id === actor.platformAdminUserId &&
    input.status === "DISABLED"
  ) {
    throw new AppError("You cannot disable your own platform admin account.", 400, "SELF_UPDATE_FORBIDDEN");
  }

  const nextEmail = (input.email ?? existing.email).trim();
  const nextEmailNormalized = normalizeEmailAddress(nextEmail);
  if (nextEmailNormalized !== existing.emailNormalized) {
    await assertPlatformAdminEmailAvailable(db, nextEmailNormalized, undefined, existing.id);
  }

  return db.runTransaction(async (tx) => {
    const ref = db.collection(COLLECTIONS.platformAdminUsers).doc(platformAdminUserId);
    const snapshot = await tx.get(ref);
    invariant(snapshot.exists, "Platform admin account not found.", 404, "PLATFORM_ADMIN_NOT_FOUND");

    const current = snapshot.data() as PlatformAdminUserDoc;
    if (current.id === actor.platformAdminUserId && input.status === "DISABLED") {
      throw new AppError("You cannot disable your own platform admin account.", 400, "SELF_UPDATE_FORBIDDEN");
    }

    if (nextEmailNormalized !== current.emailNormalized) {
      await assertPlatformAdminEmailAvailable(db, nextEmailNormalized, tx, current.id);
    }

    const updated: PlatformAdminUserDoc = {
      ...current,
      fullName: input.fullName?.trim() ?? current.fullName,
      email: nextEmail,
      emailNormalized: nextEmailNormalized,
      status: input.status ?? current.status,
    };

    tx.set(ref, updated);
    return updated;
  });
}

export async function createBusinessAdminUser(
  input: {
    tenantId: string;
    fullName: string;
    email: string;
  },
  actor: PlatformActor,
) {
  const db = requireDb();
  const tenant = await readDoc<TenantDoc>(db.collection(COLLECTIONS.tenants).doc(input.tenantId));
  invariant(tenant, "Tenant not found.", 404, "TENANT_NOT_FOUND");
  const emailNormalized = normalizeEmailAddress(input.email);
  await assertStaffEmailAvailable(db, emailNormalized);

  const branchRows = await readQuery<BranchDoc>(
    db.collection(COLLECTIONS.branches).where("tenantId", "==", input.tenantId),
  );
  const branchIds = branchRows.map((branch) => branch.id).sort((left, right) => left.localeCompare(right));
  const primaryBranchId = branchIds[0] ?? null;

  return withTenantTransaction(
    {
      tenantId: input.tenantId,
      actorId: actor.platformAdminUserId,
      actorRole: actor.role,
    },
    async (tx, database) => {
      await assertStaffEmailAvailable(database, emailNormalized, tx);
      const createdAt = nowIso();
      const staff: StaffUserDoc = {
        id: randomUUID(),
        tenantId: input.tenantId,
        authUserId: null,
        primaryBranchId,
        role: "MERCHANT_ADMIN",
        status: "INVITED",
        fullName: input.fullName.trim(),
        email: input.email.trim(),
        emailNormalized,
        branchIds,
        createdAt,
      };

      tx.set(database.collection(COLLECTIONS.staffUsers).doc(staff.id), staff);
      for (const branchId of branchIds) {
        const assignment: StaffBranchAssignmentDoc = {
          id: staffBranchAssignmentId(staff.id, branchId),
          tenantId: input.tenantId,
          staffUserId: staff.id,
          branchId,
          isPrimary: branchId === primaryBranchId,
          createdAt,
        };
        tx.set(
          database.collection(COLLECTIONS.staffBranchAssignments).doc(assignment.id),
          assignment,
        );
      }

      return staff;
    },
  );
}

export async function updateBusinessAdminStatus(
  staffUserId: string,
  input: {
    status: "ACTIVE" | "DISABLED";
  },
  actor: PlatformActor,
) {
  const db = requireDb();
  const existing = await readDoc<StaffUserDoc>(
    db.collection(COLLECTIONS.staffUsers).doc(staffUserId),
  );
  invariant(existing, "Business admin account not found.", 404, "STAFF_NOT_FOUND");
  invariant(
    existing.role === "MERCHANT_ADMIN",
    "Only business admin accounts can be managed from the platform console.",
    400,
    "ROLE_MISMATCH",
  );

  return withTenantTransaction(
    {
      tenantId: existing.tenantId,
      actorId: actor.platformAdminUserId,
      actorRole: actor.role,
    },
    async (tx, database) => {
      const current = await readDoc<StaffUserDoc>(
        database.collection(COLLECTIONS.staffUsers).doc(staffUserId),
        tx,
      );
      invariant(current, "Business admin account not found.", 404, "STAFF_NOT_FOUND");
      invariant(
        current.role === "MERCHANT_ADMIN",
        "Only business admin accounts can be managed from the platform console.",
        400,
        "ROLE_MISMATCH",
      );

      const updated: StaffUserDoc = {
        ...current,
        status: input.status,
      };
      tx.set(database.collection(COLLECTIONS.staffUsers).doc(current.id), updated);
      return updated;
    },
  );
}

export async function listBranches(actor: StaffActor) {
  const db = requireDb();
  const snapshot = await db
    .collection(COLLECTIONS.branches)
    .where("tenantId", "==", actor.tenantId)
    .get();

  return snapshot.docs
    .map((doc) => doc.data() as BranchDoc)
    .sort((left, right) => left.name.localeCompare(right.name));
}

export async function listPlans(actor: StaffActor) {
  const db = requireDb();
  const snapshot = await db
    .collection(COLLECTIONS.plans)
    .where("tenantId", "==", actor.tenantId)
    .get();

  return snapshot.docs
    .map((doc) => doc.data() as PlanDoc)
    .sort((left, right) => right.updatedAt.localeCompare(left.updatedAt));
}

export async function listStaffUsers(actor: StaffActor) {
  const db = requireDb();
  const snapshot = await db
    .collection(COLLECTIONS.staffUsers)
    .where("tenantId", "==", actor.tenantId)
    .get();

  return snapshot.docs
    .map((doc) => doc.data() as StaffUserDoc)
    .sort((left, right) => left.fullName.localeCompare(right.fullName));
}

export async function getPlatformOverviewReport() {
  const db = requireDb();
  const [
    tenants,
    branches,
    plans,
    staffUsers,
    platformAdmins,
    memberships,
    securityEvents,
  ] = await Promise.all([
    db.collection(COLLECTIONS.tenants).get(),
    db.collection(COLLECTIONS.branches).get(),
    db.collection(COLLECTIONS.plans).get(),
    db.collection(COLLECTIONS.staffUsers).get(),
    db.collection(COLLECTIONS.platformAdminUsers).get(),
    db.collection(COLLECTIONS.memberships).get(),
    db.collection(COLLECTIONS.securityEvents).get(),
  ]);

  return {
    totalTenants: tenants.size,
    activeTenants: tenants.docs.filter((doc) => (doc.data() as TenantDoc).status === "ACTIVE").length,
    totalBranches: branches.size,
    totalPlans: plans.size,
    totalMerchantStaff: staffUsers.size,
    totalBusinessAdmins: staffUsers.docs.filter(
      (doc) => (doc.data() as StaffUserDoc).role === "MERCHANT_ADMIN",
    ).length,
    totalPlatformAdmins: platformAdmins.size,
    totalMemberships: memberships.size,
    totalSecurityEvents: securityEvents.size,
  };
}

export async function listPlatformAdminUsers() {
  const db = requireDb();
  const snapshot = await db.collection(COLLECTIONS.platformAdminUsers).get();

  return snapshot.docs
    .map((doc) => doc.data() as PlatformAdminUserDoc)
    .sort((left, right) => right.createdAt.localeCompare(left.createdAt));
}

export async function listTenantDirectory() {
  const db = requireDb();
  const [tenants, branches, plans, staffUsers, memberships] = await Promise.all([
    db.collection(COLLECTIONS.tenants).get(),
    db.collection(COLLECTIONS.branches).get(),
    db.collection(COLLECTIONS.plans).get(),
    db.collection(COLLECTIONS.staffUsers).get(),
    db.collection(COLLECTIONS.memberships).get(),
  ]);

  const branchCountByTenant = new Map<string, number>();
  const planCountByTenant = new Map<string, number>();
  const membershipCountByTenant = new Map<string, number>();
  const merchantAdminsByTenant = new Map<string, string[]>();
  const staffCountByTenant = new Map<string, number>();

  for (const branch of branches.docs.map((doc) => doc.data() as BranchDoc)) {
    branchCountByTenant.set(branch.tenantId, (branchCountByTenant.get(branch.tenantId) ?? 0) + 1);
  }

  for (const plan of plans.docs.map((doc) => doc.data() as PlanDoc)) {
    planCountByTenant.set(plan.tenantId, (planCountByTenant.get(plan.tenantId) ?? 0) + 1);
  }

  for (const membership of memberships.docs.map((doc) => doc.data() as MembershipDoc)) {
    membershipCountByTenant.set(
      membership.tenantId,
      (membershipCountByTenant.get(membership.tenantId) ?? 0) + 1,
    );
  }

  for (const staff of staffUsers.docs.map((doc) => doc.data() as StaffUserDoc)) {
    staffCountByTenant.set(staff.tenantId, (staffCountByTenant.get(staff.tenantId) ?? 0) + 1);
    if (staff.role === "MERCHANT_ADMIN") {
      const admins = merchantAdminsByTenant.get(staff.tenantId) ?? [];
      admins.push(staff.email);
      merchantAdminsByTenant.set(staff.tenantId, admins);
    }
  }

  return tenants.docs
    .map((doc) => doc.data() as TenantDoc)
    .map((tenant) => ({
      id: tenant.id,
      slug: tenant.slug,
      name: tenant.name,
      status: tenant.status,
      createdAt: tenant.createdAt,
      branchCount: branchCountByTenant.get(tenant.id) ?? 0,
      planCount: planCountByTenant.get(tenant.id) ?? 0,
      staffCount: staffCountByTenant.get(tenant.id) ?? 0,
      membershipCount: membershipCountByTenant.get(tenant.id) ?? 0,
      merchantAdmins: merchantAdminsByTenant.get(tenant.id) ?? [],
    }))
    .sort((left, right) => left.name.localeCompare(right.name));
}

export async function getOverviewReport(actor: StaffActor) {
  const db = requireDb();
  const [memberships, rewards, branches, staffUsers] = await Promise.all([
    db.collection(COLLECTIONS.memberships).where("tenantId", "==", actor.tenantId).get(),
    db
      .collection(COLLECTIONS.ledgerEvents)
      .where("tenantId", "==", actor.tenantId)
      .where("eventType", "==", "REWARD_REDEEMED")
      .get(),
    db.collection(COLLECTIONS.branches).where("tenantId", "==", actor.tenantId).get(),
    db.collection(COLLECTIONS.staffUsers).where("tenantId", "==", actor.tenantId).get(),
  ]);

  return {
    totalMemberships: memberships.size,
    activeMemberships: memberships.docs.filter(
      (doc) => (doc.data() as MembershipDoc).status === "ACTIVE",
    ).length,
    rewardsRedeemed: rewards.size,
    branchCount: branches.size,
    managerCount: staffUsers.docs.filter(
      (doc) => (doc.data() as StaffUserDoc).role === "MANAGER",
    ).length,
  };
}

export async function getStaffActivityReport(actor: StaffActor) {
  const db = requireDb();
  const [staffSnapshot, branchSnapshot, ledgerSnapshot] = await Promise.all([
    db.collection(COLLECTIONS.staffUsers).where("tenantId", "==", actor.tenantId).get(),
    db.collection(COLLECTIONS.branches).where("tenantId", "==", actor.tenantId).get(),
    db.collection(COLLECTIONS.ledgerEvents).where("tenantId", "==", actor.tenantId).get(),
  ]);

  const staffUsers = staffSnapshot.docs.map((doc) => doc.data() as StaffUserDoc);
  const branchNameById = new Map(
    branchSnapshot.docs
      .map((doc) => doc.data() as BranchDoc)
      .map((branch) => [branch.id, branch.name]),
  );

  return buildStaffPerformanceSnapshots({
    actor,
    staffUsers,
    branchNameById,
    events: ledgerSnapshot.docs.map((doc) => doc.data() as LedgerEventDoc),
  });
}

export async function getExceptionsReport(actor: StaffActor) {
  const db = requireDb();
  const query = db
    .collection(COLLECTIONS.securityEvents)
    .where("tenantId", "==", actor.tenantId)
    .orderBy("createdAt", "desc")
    .limit(50);

  const rows = await query.get();
  return rows.docs.map((doc) => doc.data() as SecurityEventDoc);
}

export async function getPlatformExceptionsReport() {
  const db = requireDb();
  const rows = await db
    .collection(COLLECTIONS.securityEvents)
    .orderBy("createdAt", "desc")
    .limit(50)
    .get();

  return rows.docs.map((doc) => doc.data() as SecurityEventDoc);
}

export async function listBusinessAdminDirectory() {
  const db = requireDb();
  const [tenantSnapshot, branchSnapshot, staffSnapshot] = await Promise.all([
    db.collection(COLLECTIONS.tenants).get(),
    db.collection(COLLECTIONS.branches).get(),
    db.collection(COLLECTIONS.staffUsers).where("role", "==", "MERCHANT_ADMIN").get(),
  ]);

  const tenantById = new Map(
    tenantSnapshot.docs
      .map((doc) => doc.data() as TenantDoc)
      .map((tenant) => [tenant.id, tenant]),
  );
  const branchNameById = new Map(
    branchSnapshot.docs
      .map((doc) => doc.data() as BranchDoc)
      .map((branch) => [branch.id, branch.name]),
  );

  return staffSnapshot.docs
    .map((doc) => doc.data() as StaffUserDoc)
    .map((staff) => {
      const tenant = tenantById.get(staff.tenantId);

      return {
        id: staff.id,
        tenantId: staff.tenantId,
        tenantName: tenant?.name ?? staff.tenantId,
        tenantSlug: tenant?.slug ?? staff.tenantId,
        fullName: staff.fullName,
        email: staff.email,
        status: staff.status,
        primaryBranchName: staff.primaryBranchId
          ? branchNameById.get(staff.primaryBranchId) ?? staff.primaryBranchId
          : null,
        branchCount: dedupeIds(staff.branchIds ?? []).length,
        authLinked: Boolean(staff.authUserId),
        createdAt: staff.createdAt,
      } satisfies BusinessAdminDirectoryEntry;
    })
    .sort((left, right) => {
      const tenantOrder = left.tenantName.localeCompare(right.tenantName);
      return tenantOrder !== 0 ? tenantOrder : left.fullName.localeCompare(right.fullName);
    });
}
