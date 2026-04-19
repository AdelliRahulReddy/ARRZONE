import "server-only";
import type { DecodedIdToken } from "firebase-admin/auth";
import type {
  PlatformAdminUserDoc,
  StaffRole,
  StaffStatus,
  StaffUserDoc,
} from "@/lib/firebase/model";
import { AppError } from "@/lib/server/errors";
import { getFirebaseAdminAuth } from "@/lib/server/firebase-auth";

const ACCESS_CLAIM_KEY = "arrzAccess";
const ACCESS_CLAIM_VERSION = 1;

type StaffAccessClaim = {
  v: 1;
  actorType: "staff";
  staffUserId: string;
  tenantId: string;
  role: StaffRole;
  branchIds: string[];
  status: StaffStatus;
  authUserId: string;
};

type PlatformAccessClaim = {
  v: 1;
  actorType: "platform_admin";
  platformAdminUserId: string;
  role: "PLATFORM_ADMIN";
  status: StaffStatus;
  authUserId: string;
};

export type AccessClaim = StaffAccessClaim | PlatformAccessClaim;

function asStringArray(value: unknown) {
  if (!Array.isArray(value) || value.some((item) => typeof item !== "string")) {
    return null;
  }

  return value;
}

function getRawAccessClaim(session: Pick<DecodedIdToken, "uid"> & Record<string, unknown>) {
  const rawClaim = session[ACCESS_CLAIM_KEY];
  if (!rawClaim || typeof rawClaim !== "object") {
    return null;
  }

  return rawClaim as Record<string, unknown>;
}

export function readAccessClaim(
  session: Pick<DecodedIdToken, "uid"> & Record<string, unknown>,
): AccessClaim | null {
  const rawClaim = getRawAccessClaim(session);
  if (!rawClaim) {
    return null;
  }

  if (rawClaim.v !== ACCESS_CLAIM_VERSION || rawClaim.authUserId !== session.uid) {
    return null;
  }

  if (rawClaim.actorType === "staff") {
    const branchIds = asStringArray(rawClaim.branchIds);
    if (
      typeof rawClaim.staffUserId === "string" &&
      typeof rawClaim.tenantId === "string" &&
      typeof rawClaim.role === "string" &&
      typeof rawClaim.status === "string" &&
      branchIds
    ) {
      return {
        v: ACCESS_CLAIM_VERSION,
        actorType: "staff",
        staffUserId: rawClaim.staffUserId,
        tenantId: rawClaim.tenantId,
        role: rawClaim.role as StaffRole,
        branchIds,
        status: rawClaim.status as StaffStatus,
        authUserId: rawClaim.authUserId,
      };
    }

    return null;
  }

  if (
    rawClaim.actorType === "platform_admin" &&
    typeof rawClaim.platformAdminUserId === "string" &&
    rawClaim.role === "PLATFORM_ADMIN" &&
    typeof rawClaim.status === "string"
  ) {
    return {
      v: ACCESS_CLAIM_VERSION,
      actorType: "platform_admin",
      platformAdminUserId: rawClaim.platformAdminUserId,
      role: "PLATFORM_ADMIN",
      status: rawClaim.status as StaffStatus,
      authUserId: rawClaim.authUserId,
    };
  }

  return null;
}

export function assertClaimStatus(
  claim: Pick<AccessClaim, "actorType" | "status">,
) {
  const isPlatform = claim.actorType === "platform_admin";
  const prefix = isPlatform ? "PLATFORM_ADMIN" : "STAFF";
  const accountLabel = isPlatform ? "platform admin" : "staff";

  if (claim.status === "DISABLED") {
    throw new AppError(
      `This ${accountLabel} account is disabled.`,
      403,
      `${prefix}_DISABLED`,
    );
  }

  if (claim.status !== "ACTIVE") {
    throw new AppError(
      `This ${accountLabel} account is not active.`,
      403,
      `${prefix}_NOT_ACTIVE`,
    );
  }
}

function toStaffAccessClaim(staffUser: StaffUserDoc) {
  if (!staffUser.authUserId) {
    return null;
  }

  return {
    v: ACCESS_CLAIM_VERSION,
    actorType: "staff",
    staffUserId: staffUser.id,
    tenantId: staffUser.tenantId,
    role: staffUser.role,
    branchIds: staffUser.branchIds ?? [],
    status: staffUser.status,
    authUserId: staffUser.authUserId,
  } satisfies StaffAccessClaim;
}

function toPlatformAccessClaim(platformAdmin: PlatformAdminUserDoc) {
  if (!platformAdmin.authUserId) {
    return null;
  }

  return {
    v: ACCESS_CLAIM_VERSION,
    actorType: "platform_admin",
    platformAdminUserId: platformAdmin.id,
    role: "PLATFORM_ADMIN",
    status: platformAdmin.status,
    authUserId: platformAdmin.authUserId,
  } satisfies PlatformAccessClaim;
}

async function updateCustomClaims(uid: string, nextAccessClaim: AccessClaim | null) {
  const auth = getFirebaseAdminAuth();
  const userRecord = await auth.getUser(uid);
  const currentClaims = { ...(userRecord.customClaims ?? {}) };
  const currentAccessClaim = currentClaims[ACCESS_CLAIM_KEY] ?? null;

  if (JSON.stringify(currentAccessClaim) === JSON.stringify(nextAccessClaim)) {
    return;
  }

  if (nextAccessClaim) {
    currentClaims[ACCESS_CLAIM_KEY] = nextAccessClaim;
  } else {
    delete currentClaims[ACCESS_CLAIM_KEY];
  }

  await auth.setCustomUserClaims(uid, currentClaims);
  await auth.revokeRefreshTokens(uid);
}

export async function syncStaffAccessClaims(staffUser: StaffUserDoc) {
  if (!staffUser.authUserId) {
    return;
  }

  await updateCustomClaims(staffUser.authUserId, toStaffAccessClaim(staffUser));
}

export async function syncPlatformAccessClaims(platformAdmin: PlatformAdminUserDoc) {
  if (!platformAdmin.authUserId) {
    return;
  }

  await updateCustomClaims(
    platformAdmin.authUserId,
    toPlatformAccessClaim(platformAdmin),
  );
}
