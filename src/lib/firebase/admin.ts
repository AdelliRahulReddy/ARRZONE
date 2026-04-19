import "server-only";
import {
  applicationDefault,
  cert,
  getApps,
  initializeApp,
} from "firebase-admin/app";
import { getFirestore } from "firebase-admin/firestore";
import { appEnv } from "@/lib/env";
import { AppError } from "@/lib/server/errors";

function getFirebaseApp() {
  const [existing] = getApps();
  if (existing) {
    return existing;
  }

  if (!appEnv.firebaseProjectId) {
    throw new AppError(
      "FIREBASE_PROJECT_ID is not configured.",
      503,
      "SETUP_REQUIRED",
    );
  }

  if (process.env.FIRESTORE_EMULATOR_HOST) {
    return initializeApp({
      projectId: appEnv.firebaseProjectId,
    });
  }

  if (appEnv.firebaseClientEmail && appEnv.firebasePrivateKey) {
    return initializeApp({
      credential: cert({
        projectId: appEnv.firebaseProjectId,
        clientEmail: appEnv.firebaseClientEmail,
        privateKey: appEnv.firebasePrivateKey,
      }),
      projectId: appEnv.firebaseProjectId,
    });
  }

  if (process.env.GOOGLE_APPLICATION_CREDENTIALS) {
    return initializeApp({
      credential: applicationDefault(),
      projectId: appEnv.firebaseProjectId,
    });
  }

  throw new AppError(
    "Firebase Admin is not configured. Set FIREBASE_PROJECT_ID plus service-account credentials or GOOGLE_APPLICATION_CREDENTIALS.",
    503,
    "SETUP_REQUIRED",
  );
}

export function getFirestoreAdmin() {
  return getFirestore(getFirebaseApp());
}
