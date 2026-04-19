import { createHash } from "node:crypto";
import type { LoyaltyEventType } from "@/lib/domain/loyalty";

export type TimestampString = string;

export const COLLECTIONS = {
  activeMembershipLookups: "active_membership_lookups",
  branches: "branches",
  branchCodeLookups: "branch_code_lookups",
  customers: "customers",
  customerPhoneLookups: "customer_phone_lookups",
  enrollmentConsents: "enrollment_consents",
  idempotencyRequests: "idempotency_requests",
  ledgerEvents: "ledger_events",
  memberPasses: "member_passes",
  membershipMerges: "membership_merges",
  memberships: "memberships",
  membershipSummaries: "membership_summaries",
  platformAdminUsers: "platform_admin_users",
  plans: "plans",
  planVersions: "plan_versions",
  redeemTokens: "redeem_tokens",
  securityEvents: "security_events",
  staffBranchAssignments: "staff_branch_assignments",
  staffUsers: "staff_users",
  tenants: "tenants",
} as const;

export type StaffRole =
  | "CASHIER"
  | "MANAGER"
  | "MERCHANT_ADMIN";

export type StaffStatus = "INVITED" | "ACTIVE" | "DISABLED";
export type TenantStatus = "ACTIVE" | "SUSPENDED" | "ARCHIVED";
export type BranchStatus = "ACTIVE" | "INACTIVE";
export type PlanStatus = "ACTIVE" | "INACTIVE";
export type CustomerStatus = "ACTIVE" | "BLOCKED" | "DELETED";
export type MembershipStatus = "ACTIVE" | "INACTIVE" | "MERGED" | "REVOKED";
export type PassStatus = "ACTIVE" | "REVOKED";

export type TenantDoc = {
  id: string;
  slug: string;
  name: string;
  status: TenantStatus;
  branding: Record<string, unknown>;
  createdAt: TimestampString;
};

export type BranchDoc = {
  id: string;
  tenantId: string;
  code: string;
  name: string;
  timezone: string;
  address: string | null;
  status: BranchStatus;
  createdAt: TimestampString;
};

export type BranchCodeLookupDoc = {
  id: string;
  branchId: string;
  tenantId: string;
  status: BranchStatus;
  updatedAt: TimestampString;
};

export type PlanDoc = {
  id: string;
  tenantId: string;
  name: string;
  eligibleLabel: string;
  thresholdCount: number;
  rewardCreditCount: number;
  currentVersionNumber: number;
  currentVersionId: string;
  applicableBranchIds: string[];
  status: PlanStatus;
  validityStartsAt: TimestampString | null;
  validityEndsAt: TimestampString | null;
  redemptionConstraints: Record<string, unknown>;
  createdAt: TimestampString;
  updatedAt: TimestampString;
};

export type PlanVersionDoc = {
  id: string;
  tenantId: string;
  planId: string;
  versionNumber: number;
  name: string;
  eligibleLabel: string;
  thresholdCount: number;
  rewardCreditCount: number;
  snapshot: Record<string, unknown>;
  createdAt: TimestampString;
};

export type StaffUserDoc = {
  id: string;
  tenantId: string;
  authUserId: string | null;
  primaryBranchId: string | null;
  role: StaffRole;
  status: StaffStatus;
  fullName: string;
  email: string;
  emailNormalized: string;
  branchIds: string[];
  createdAt: TimestampString;
};

export type PlatformAdminUserDoc = {
  id: string;
  authUserId: string | null;
  status: StaffStatus;
  fullName: string;
  email: string;
  emailNormalized: string;
  createdAt: TimestampString;
};

export type StaffBranchAssignmentDoc = {
  id: string;
  tenantId: string;
  staffUserId: string;
  branchId: string;
  isPrimary: boolean;
  createdAt: TimestampString;
};

export type CustomerDoc = {
  id: string;
  tenantId: string;
  fullName: string;
  normalizedPhone: string;
  email: string | null;
  phoneVerified: boolean;
  status: CustomerStatus;
  createdAt: TimestampString;
};

export type CustomerPhoneLookupDoc = {
  id: string;
  customerId: string;
  tenantId: string;
  normalizedPhone: string;
  updatedAt: TimestampString;
};

export type MembershipDoc = {
  id: string;
  tenantId: string;
  customerId: string;
  enrolledBranchId: string;
  planId: string;
  planVersionId: string;
  status: MembershipStatus;
  mergedIntoMembershipId: string | null;
  canonicalMembershipId: string;
  mergedMembershipIds: string[];
  startedAt: TimestampString;
  activePassId: string | null;
  activePassVersion: number | null;
  currentRedeemTokenId: string | null;
  currentRedeemTokenPassId: string | null;
  lastIssuedPassVersion: number;
  updatedAt: TimestampString;
};

export type ActiveMembershipLookupDoc = {
  id: string;
  membershipId: string;
  tenantId: string;
  customerId: string;
  planId: string;
  status: MembershipStatus;
  updatedAt: TimestampString;
};

export type MemberPassDoc = {
  id: string;
  tenantId: string;
  membershipId: string;
  passVersion: number;
  tokenId: string;
  status: PassStatus;
  currentRedeemTokenId: string | null;
  issuedAt: TimestampString;
  revokedAt: TimestampString | null;
  updatedAt: TimestampString;
};

export type RedeemTokenDoc = {
  id: string;
  tenantId: string;
  passId: string;
  membershipId: string;
  tokenHash: string;
  tokenPreview: string;
  expiresAt: TimestampString;
  consumedAt: TimestampString | null;
  revokedAt: TimestampString | null;
  issuedAt: TimestampString;
  updatedAt: TimestampString;
};

export type LedgerEventDoc = {
  id: string;
  tenantId: string;
  branchId: string | null;
  membershipId: string;
  customerId: string;
  eventType: LoyaltyEventType;
  quantity: number;
  unlockCycle: number | null;
  reasonCode: string | null;
  source: string;
  idempotencyKey: string | null;
  createdByStaffUserId: string | null;
  metadata: Record<string, unknown>;
  createdAt: TimestampString;
};

export type MembershipSummaryDoc = {
  id: string;
  membershipId: string;
  tenantId: string;
  purchaseCount: number;
  rewardEarnedCount: number;
  rewardRedeemedCount: number;
  rewardBalance: number;
  lastActivityAt: TimestampString | null;
  updatedAt: TimestampString;
  lineageMembershipIds: string[];
  canonicalMembershipId: string;
};

export type EnrollmentConsentDoc = {
  id: string;
  tenantId: string;
  customerId: string;
  membershipId: string;
  consentVersion: string;
  consentedAt: TimestampString;
  ipHash: string | null;
};

export type IdempotencyRequestDoc = {
  id: string;
  tenantId: string;
  operation: string;
  idempotencyKey: string;
  requestHash: string;
  responseStatus: number;
  responseBody: Record<string, unknown>;
  createdAt: TimestampString;
};

export type SecurityEventDoc = {
  id: string;
  tenantId: string | null;
  branchId: string | null;
  staffUserId: string | null;
  eventType: string;
  scopeKey: string;
  subjectKey: string | null;
  ipHash: string | null;
  metadata: Record<string, unknown>;
  createdAt: TimestampString;
};

export type MembershipMergeDoc = {
  id: string;
  tenantId: string;
  survivorMembershipId: string;
  obsoleteMembershipId: string;
  reasonCode: string;
  actorStaffUserId: string;
  createdAt: TimestampString;
};

function hashKey(input: string) {
  return createHash("sha256").update(input).digest("hex");
}

export function nowIso(date = new Date()) {
  return date.toISOString();
}

export function phoneLookupId(tenantId: string, normalizedPhone: string) {
  return hashKey(`${tenantId}:${normalizedPhone}`);
}

export function activeMembershipLookupId(
  tenantId: string,
  customerId: string,
  planId: string,
) {
  return hashKey(`${tenantId}:${customerId}:${planId}`);
}

export function idempotencyRequestId(
  tenantId: string,
  operation: string,
  idempotencyKey: string,
) {
  return hashKey(`${tenantId}:${operation}:${idempotencyKey}`);
}

export function rewardUnlockEventId(
  membershipId: string,
  unlockCycle: number,
) {
  return `reward_unlock:${membershipId}:${unlockCycle}`;
}

export function staffBranchAssignmentId(
  staffUserId: string,
  branchId: string,
) {
  return `${staffUserId}:${branchId}`;
}

export function membershipMergeId(
  survivorMembershipId: string,
  obsoleteMembershipId: string,
) {
  return `${survivorMembershipId}:${obsoleteMembershipId}`;
}
