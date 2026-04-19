import {
  getAuthSurface,
  getDefaultRedirectForStaffRole,
  getMinimumRoleForSurface,
  sanitizeRedirectTarget,
} from "@/lib/auth/presentation";
import { authSessionSchema } from "@/lib/validation";
import { jsonError, jsonOk } from "@/lib/server/api";
import { STAFF_SESSION_COOKIE_NAME, STAFF_SESSION_TTL_SECONDS } from "@/lib/auth/constants";
import { createStaffSessionCookie, verifyStaffIdToken } from "@/lib/server/firebase-auth";
import {
  assertStaffActorRole,
  resolvePlatformActorFromClaims,
  resolveStaffActorFromClaims,
} from "@/lib/server/auth";
import { AppError } from "@/lib/server/errors";

function shouldFallbackToPlatform(error: unknown) {
  return (
    error instanceof AppError &&
    error.code === "STAFF_MAPPING_MISSING"
  );
}

export async function POST(request: Request) {
  try {
    const { idToken, redirectTo } = authSessionSchema.parse(await request.json());
    const decodedToken = await verifyStaffIdToken(idToken);
    const safeRedirectTarget = sanitizeRedirectTarget(redirectTo);
    const requestedSurface = safeRedirectTarget
      ? getAuthSurface(safeRedirectTarget)
      : null;
    let nextRedirect: string;

    if (requestedSurface === "platform") {
      const actor = await resolvePlatformActorFromClaims(decodedToken);
      if (!actor) {
        throw new AppError("A valid Firebase session is required.", 401, "UNAUTHORIZED");
      }
      nextRedirect = safeRedirectTarget ?? "/platform";
    } else {
      try {
        const actor = await resolveStaffActorFromClaims(decodedToken);
        if (!actor) {
          throw new AppError("A valid Firebase session is required.", 401, "UNAUTHORIZED");
        }

        const minimumRole = requestedSurface
          ? getMinimumRoleForSurface(requestedSurface)
          : null;
        if (minimumRole) {
          assertStaffActorRole(actor, minimumRole);
        }

        nextRedirect = safeRedirectTarget ?? getDefaultRedirectForStaffRole(actor.role);
      } catch (error) {
        if (!requestedSurface && shouldFallbackToPlatform(error)) {
          const platformActor = await resolvePlatformActorFromClaims(decodedToken);
          if (!platformActor) {
            throw new AppError("A valid Firebase session is required.", 401, "UNAUTHORIZED");
          }
          nextRedirect = "/platform";
        } else {
          throw error;
        }
      }
    }

    const sessionCookie = await createStaffSessionCookie(idToken);
    const response = jsonOk({ sessionStarted: true, redirectTo: nextRedirect });
    response.cookies.set(STAFF_SESSION_COOKIE_NAME, sessionCookie, {
      httpOnly: true,
      maxAge: STAFF_SESSION_TTL_SECONDS,
      path: "/",
      sameSite: "lax",
      secure: process.env.NODE_ENV === "production",
    });
    return response;
  } catch (error) {
    return jsonError(error);
  }
}
