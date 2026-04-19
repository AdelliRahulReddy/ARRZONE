import { config } from "dotenv";
import { applicationDefault, cert, getApps, initializeApp } from "firebase-admin/app";
import { FieldValue, getFirestore } from "firebase-admin/firestore";

config({ path: ".env.local" });
config();

function normalizeEnv(value) {
  return value ? value.trim() : "";
}

function normalizePrivateKey(value) {
  return normalizeEnv(value).replace(/\\n/g, "\n");
}

function normalizeEmailAddress(email) {
  return String(email || "").trim().toLowerCase();
}

function getFirebaseApp() {
  if (getApps().length > 0) {
    return getApps()[0];
  }

  const projectId = normalizeEnv(process.env.FIREBASE_PROJECT_ID);
  const clientEmail = normalizeEnv(process.env.FIREBASE_CLIENT_EMAIL);
  const privateKey = normalizePrivateKey(process.env.FIREBASE_PRIVATE_KEY);

  if (!projectId) {
    throw new Error("FIREBASE_PROJECT_ID is required.");
  }

  if (process.env.FIRESTORE_EMULATOR_HOST) {
    return initializeApp({ projectId });
  }

  if (clientEmail && privateKey) {
    return initializeApp({
      credential: cert({
        projectId,
        clientEmail,
        privateKey,
      }),
      projectId,
    });
  }

  if (process.env.GOOGLE_APPLICATION_CREDENTIALS) {
    return initializeApp({
      credential: applicationDefault(),
      projectId,
    });
  }

  throw new Error(
    "Firebase Admin credentials are not configured. Set FIREBASE_PROJECT_ID plus service-account credentials or GOOGLE_APPLICATION_CREDENTIALS.",
  );
}

async function main() {
  const db = getFirestore(getFirebaseApp());
  const snapshot = await db.collection("staff_users").get();

  if (snapshot.empty) {
    console.log("No staff_users documents found.");
    return;
  }

  let batch = db.batch();
  let pendingWrites = 0;
  let migrated = 0;

  for (const doc of snapshot.docs) {
    const data = doc.data();
    batch.set(
      doc.ref,
      {
        authUserId:
          data.authUserId === undefined ? null : data.authUserId,
        emailNormalized: normalizeEmailAddress(data.email),
        clerkUserId: FieldValue.delete(),
      },
      { merge: true },
    );
    pendingWrites += 1;
    migrated += 1;

    if (pendingWrites === 400) {
      await batch.commit();
      batch = db.batch();
      pendingWrites = 0;
    }
  }

  if (pendingWrites > 0) {
    await batch.commit();
  }

  console.log(`Migrated ${migrated} staff_users documents.`);
}

main().catch((error) => {
  console.error(error);
  process.exitCode = 1;
});
