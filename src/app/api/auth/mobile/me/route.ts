import { jsonError, jsonOk } from "@/lib/server/api";
import { getDefaultRedirectForStaffRole } from "@/lib/auth/presentation";
import {
  resolvePlatformActorFromClaims,
  resolveStaffActorFromClaims,
} from "@/lib/server/auth";
import { getCurrentStaffSessionClaims } from "@/lib/server/firebase-auth";
import { AppError } from "@/lib/server/errors";

function shouldFallbackToPlatform(error: unknown) {
  return error instanceof AppError && error.code === "STAFF_MAPPING_MISSING";
}

export async function GET(request: Request) {
  try {
    const claims = await getCurrentStaffSessionClaims({ request });
    if (!claims) {
      throw new AppError("A valid Firebase bearer token is required.", 401, "UNAUTHORIZED");
    }

    try {
      const actor = await resolveStaffActorFromClaims(claims);
      if (!actor) {
        throw new AppError("A valid sign-in session is required.", 401, "UNAUTHORIZED");
      }

      return jsonOk({
        authMode: "bearer",
        actorType: "staff",
        userId: actor.userId,
        authUserId: actor.authUserId,
        staffUserId: actor.staffUserId,
        tenantId: actor.tenantId,
        role: actor.role,
        branchIds: actor.branchIds,
        availableSurfaces:
          actor.role === "MERCHANT_ADMIN" ? ["staff", "merchant"] : ["staff"],
        defaultRoute: getDefaultRedirectForStaffRole(actor.role),
      });
    } catch (error) {
      if (!shouldFallbackToPlatform(error)) {
        throw error;
      }

      const actor = await resolvePlatformActorFromClaims(claims);
      if (!actor) {
        throw new AppError("A valid sign-in session is required.", 401, "UNAUTHORIZED");
      }

      return jsonOk({
        authMode: "bearer",
        actorType: "platform_admin",
        userId: actor.userId,
        authUserId: actor.authUserId,
        platformAdminUserId: actor.platformAdminUserId,
        role: actor.role,
        availableSurfaces: ["platform"],
        defaultRoute: "/platform",
      });
    }
  } catch (error) {
    return jsonError(error);
  }
}
