import "server-only";
import {
  COLLECTIONS,
  type PlatformAdminUserDoc,
  type StaffRole,
  type StaffStatus,
  type StaffUserDoc,
} from "@/lib/firebase/model";
import { normalizeEmailAddress } from "@/lib/email";
import {
  assertClaimStatus,
  readAccessClaim,
  syncPlatformAccessClaims,
  syncStaffAccessClaims,
} from "@/lib/server/access-claims";
import {
  getCurrentStaffSessionClaims,
  type StaffSessionClaims,
} from "@/lib/server/firebase-auth";
import { requireDb } from "@/lib/server/db";
import { AppError } from "@/lib/server/errors";

export type { StaffRole };

export type StaffActor = {
  userId: string;
  staffUserId: string;
  tenantId: string;
  role: StaffRole;
  branchIds: string[];
  authUserId: string;
};

export type PlatformActor = {
  userId: string;
  platformAdminUserId: string;
  role: "PLATFORM_ADMIN";
  authUserId: string;
};

type AccessRecord = {
  id: string;
  authUserId: string | null;
  status: StaffStatus;
  email: string;
  emailNormalized: string;
};

type AccessResolutionOptions<TRecord extends AccessRecord> = {
  collectionName: string;
  codePrefix: "STAFF" | "PLATFORM_ADMIN";
  accountLabel: string;
  castRecord: (value: unknown) => TRecord;
};

const roleRank: Record<StaffRole, number> = {
  CASHIER: 1,
  MANAGER: 2,
  MERCHANT_ADMIN: 3,
};

function accessCode(
  prefix: AccessResolutionOptions<AccessRecord>["codePrefix"],
  suffix: string,
) {
  return `${prefix}_${suffix}`;
}

function assertResolvedRecordStatus(
  record: AccessRecord,
  options: AccessResolutionOptions<AccessRecord>,
) {
  if (record.status === "DISABLED") {
    throw new AppError(
      `This ${options.accountLabel} account is disabled.`,
      403,
      accessCode(options.codePrefix, "DISABLED"),
    );
  }

  if (record.status !== "ACTIVE") {
    throw new AppError(
      `This ${options.accountLabel} account is not active.`,
      403,
      accessCode(options.codePrefix, "NOT_ACTIVE"),
    );
  }
}

async function resolveAccessRecordFromClaims<TRecord extends AccessRecord>(
  session: Pick<StaffSessionClaims, "uid" | "email" | "email_verified"> | null,
  options: AccessResolutionOptions<TRecord>,
) {
  if (!session?.uid) {
    return null;
  }

  const db = requireDb();
  const boundSnapshot = await db
    .collection(options.collectionName)
    .where("authUserId", "==", session.uid)
    .limit(2)
    .get();

  if (boundSnapshot.size > 1) {
    throw new AppError(
      `This sign-in account is mapped to multiple ${options.accountLabel} records.`,
      409,
      accessCode(options.codePrefix, "MAPPING_AMBIGUOUS"),
    );
  }

  if (boundSnapshot.size === 1) {
    const record = options.castRecord(boundSnapshot.docs[0].data());
    assertResolvedRecordStatus(record, options);
    return record;
  }

  const email = session.email ? normalizeEmailAddress(session.email) : "";
  if (!email || session.email_verified !== true) {
    throw new AppError(
      `A verified email address is required for ${options.accountLabel} access.`,
      403,
      accessCode(options.codePrefix, "EMAIL_NOT_VERIFIED"),
    );
  }

  const emailSnapshot = await db
    .collection(options.collectionName)
    .where("emailNormalized", "==", email)
    .limit(5)
    .get();

  if (emailSnapshot.empty) {
    throw new AppError(
      `No ${options.accountLabel} record matched this sign-in account.`,
      403,
      accessCode(options.codePrefix, "MAPPING_MISSING"),
    );
  }

  const records = emailSnapshot.docs.map((doc) => options.castRecord(doc.data()));
  const activeBinding = records.find((record) => record.authUserId);
  if (activeBinding) {
    throw new AppError(
      "This email is already linked to another sign-in account.",
      409,
      accessCode(options.codePrefix, "ALREADY_BOUND"),
    );
  }

  const candidates = records.filter((record) =>
    record.authUserId === null &&
    (record.status === "INVITED" || record.status === "ACTIVE"),
  );

  if (candidates.length > 1) {
    throw new AppError(
      `This email matches multiple ${options.accountLabel} records.`,
      409,
      accessCode(options.codePrefix, "MAPPING_AMBIGUOUS"),
    );
  }

  if (candidates.length === 0) {
    const disabledMatch = records.find((record) => record.status === "DISABLED");
    if (disabledMatch) {
      throw new AppError(
        `This ${options.accountLabel} account is disabled.`,
        403,
        accessCode(options.codePrefix, "DISABLED"),
      );
    }

    throw new AppError(
      `No active ${options.accountLabel} record matched this sign-in account.`,
      403,
      accessCode(options.codePrefix, "MAPPING_MISSING"),
    );
  }

  const candidate = candidates[0];
  return db.runTransaction(async (tx) => {
    const recordRef = db.collection(options.collectionName).doc(candidate.id);
    const snapshot = await tx.get(recordRef);
    if (!snapshot.exists) {
      throw new AppError(
        `No ${options.accountLabel} record matched this sign-in account.`,
        403,
        accessCode(options.codePrefix, "MAPPING_MISSING"),
      );
    }

    const record = options.castRecord(snapshot.data());
    if (record.status === "DISABLED") {
      throw new AppError(
        `This ${options.accountLabel} account is disabled.`,
        403,
        accessCode(options.codePrefix, "DISABLED"),
      );
    }

    if (record.authUserId && record.authUserId !== session.uid) {
      throw new AppError(
        "This email is already linked to another sign-in account.",
        409,
        accessCode(options.codePrefix, "ALREADY_BOUND"),
      );
    }

    const nextRecord = {
      ...record,
      authUserId: session.uid,
      status: record.status === "INVITED" ? "ACTIVE" : record.status,
      emailNormalized: normalizeEmailAddress(record.email),
    } as TRecord;
    tx.set(recordRef, nextRecord);
    return nextRecord;
  });
}

export async function resolveStaffActorFromClaims(
  session: Pick<StaffSessionClaims, "uid" | "email" | "email_verified"> | null,
) {
  if (session) {
    const accessClaim = readAccessClaim(session as StaffSessionClaims & Record<string, unknown>);
    if (accessClaim?.actorType === "staff") {
      assertClaimStatus(accessClaim);
      return {
        userId: session.uid,
        staffUserId: accessClaim.staffUserId,
        tenantId: accessClaim.tenantId,
        role: accessClaim.role,
        branchIds: accessClaim.branchIds,
        authUserId: accessClaim.authUserId,
      } satisfies StaffActor;
    }
  }

  const staffUser = await resolveAccessRecordFromClaims(session, {
    collectionName: COLLECTIONS.staffUsers,
    codePrefix: "STAFF",
    accountLabel: "staff",
    castRecord: (value) => value as StaffUserDoc,
  });

  if (!staffUser || !session?.uid) {
    return null;
  }

  if (staffUser.authUserId === session.uid) {
    await syncStaffAccessClaims(staffUser);
  }

  return {
    userId: session.uid,
    staffUserId: staffUser.id,
    tenantId: staffUser.tenantId,
    role: staffUser.role,
    branchIds: staffUser.branchIds ?? [],
    authUserId: staffUser.authUserId ?? session.uid,
  } satisfies StaffActor;
}

export async function resolvePlatformActorFromClaims(
  session: Pick<StaffSessionClaims, "uid" | "email" | "email_verified"> | null,
) {
  if (session) {
    const accessClaim = readAccessClaim(session as StaffSessionClaims & Record<string, unknown>);
    if (accessClaim?.actorType === "platform_admin") {
      assertClaimStatus(accessClaim);
      return {
        userId: session.uid,
        platformAdminUserId: accessClaim.platformAdminUserId,
        role: "PLATFORM_ADMIN",
        authUserId: accessClaim.authUserId,
      } satisfies PlatformActor;
    }
  }

  const platformAdmin = await resolveAccessRecordFromClaims(session, {
    collectionName: COLLECTIONS.platformAdminUsers,
    codePrefix: "PLATFORM_ADMIN",
    accountLabel: "platform admin",
    castRecord: (value) => value as PlatformAdminUserDoc,
  });

  if (!platformAdmin || !session?.uid) {
    return null;
  }

  if (platformAdmin.authUserId === session.uid) {
    await syncPlatformAccessClaims(platformAdmin);
  }

  return {
    userId: session.uid,
    platformAdminUserId: platformAdmin.id,
    role: "PLATFORM_ADMIN",
    authUserId: platformAdmin.authUserId ?? session.uid,
  } satisfies PlatformActor;
}

export async function getCurrentStaffActor() {
  const session = await getCurrentStaffSessionClaims();
  return resolveStaffActorFromClaims(session);
}

export async function getCurrentPlatformActor() {
  const session = await getCurrentStaffSessionClaims();
  return resolvePlatformActorFromClaims(session);
}

export function assertStaffActorRole(actor: StaffActor, minimumRole: StaffRole) {
  if (roleRank[actor.role] < roleRank[minimumRole]) {
    throw new AppError("Insufficient permissions.", 403, "FORBIDDEN");
  }
}

export async function requireStaffActor(minimumRole?: StaffRole) {
  const actor = await getCurrentStaffActor();
  if (!actor) {
    throw new AppError("Unauthorized.", 401, "UNAUTHORIZED");
  }

  if (minimumRole) {
    assertStaffActorRole(actor, minimumRole);
  }

  return actor;
}

export async function requirePlatformActor() {
  const actor = await getCurrentPlatformActor();
  if (!actor) {
    throw new AppError("Unauthorized.", 401, "UNAUTHORIZED");
  }

  return actor;
}

export function assertBranchAccess(actor: StaffActor, branchId: string) {
  if (!actor.branchIds.includes(branchId)) {
    throw new AppError(
      "You do not have access to this branch.",
      403,
      "BRANCH_FORBIDDEN",
    );
  }
}
