import "server-only";
import { getAuth, type DecodedIdToken } from "firebase-admin/auth";
import { getApps } from "firebase-admin/app";
import { cookies } from "next/headers";
import { appEnv } from "@/lib/env";
import { STAFF_SESSION_COOKIE_NAME, STAFF_SESSION_TTL_MS } from "@/lib/auth/constants";
import { AppError } from "@/lib/server/errors";
import { getFirestoreAdmin } from "@/lib/firebase/admin";

export type StaffSessionClaims = DecodedIdToken;

export function getFirebaseAdminAuth() {
  const app = getApps()[0];
  if (!app) {
    getFirestoreAdmin();
  }

  return getAuth();
}

export async function createStaffSessionCookie(idToken: string) {
  if (!appEnv.hasFirebaseAdmin) {
    throw new AppError(
      "Secure server credentials are not configured.",
      503,
      "SETUP_REQUIRED",
    );
  }

  return getFirebaseAdminAuth().createSessionCookie(idToken, {
    expiresIn: STAFF_SESSION_TTL_MS,
  });
}

export async function verifyStaffIdToken(idToken: string) {
  if (!appEnv.hasFirebaseAdmin) {
    throw new AppError(
      "Secure server credentials are not configured.",
      503,
      "SETUP_REQUIRED",
    );
  }

  try {
    return await getFirebaseAdminAuth().verifyIdToken(idToken, true);
  } catch {
    throw new AppError(
      "The sign-in token is invalid or expired. Try signing in again.",
      401,
      "UNAUTHORIZED",
    );
  }
}

export async function getCurrentStaffSessionClaims() {
  const cookieStore = await cookies();
  const sessionCookie = cookieStore.get(STAFF_SESSION_COOKIE_NAME)?.value;
  if (!sessionCookie) {
    return null;
  }

  try {
    return await getFirebaseAdminAuth().verifySessionCookie(sessionCookie, true);
  } catch {
    return null;
  }
}
