import { createHash } from "node:crypto";
import { readFileSync } from "node:fs";
import path from "node:path";
import process from "node:process";
import admin from "firebase-admin";
import { config as loadEnv } from "dotenv";

loadEnv({ path: ".env.local" });
loadEnv();

function normalizePrivateKey(value) {
  return (value ?? "").replace(/\\n/g, "\n").trim();
}

function initializeFirebaseAdmin() {
  if (admin.apps.length > 0) {
    return admin.app();
  }

  const projectId = process.env.FIREBASE_PROJECT_ID?.trim();
  const clientEmail = process.env.FIREBASE_CLIENT_EMAIL?.trim();
  const privateKey = normalizePrivateKey(process.env.FIREBASE_PRIVATE_KEY);
  const credentialsPath = process.env.GOOGLE_APPLICATION_CREDENTIALS?.trim();

  if (projectId && clientEmail && privateKey) {
    return admin.initializeApp({
      credential: admin.credential.cert({
        projectId,
        clientEmail,
        privateKey,
      }),
      projectId,
    });
  }

  if (credentialsPath) {
    const absolutePath = path.resolve(credentialsPath);
    const serviceAccount = JSON.parse(readFileSync(absolutePath, "utf8"));
    return admin.initializeApp({
      credential: admin.credential.cert(serviceAccount),
      projectId: serviceAccount.project_id ?? projectId,
    });
  }

  throw new Error(
    "Firebase Admin credentials are missing. Set GOOGLE_APPLICATION_CREDENTIALS or FIREBASE_* Admin env vars first.",
  );
}

function parseArgs(argv) {
  const entries = {};
  for (let index = 0; index < argv.length; index += 1) {
    const arg = argv[index];
    if (!arg.startsWith("--")) {
      continue;
    }

    const key = arg.slice(2);
    const next = argv[index + 1];
    if (!next || next.startsWith("--")) {
      entries[key] = "true";
      continue;
    }

    entries[key] = next;
    index += 1;
  }

  return entries;
}

function hashId(prefix, value) {
  return `${prefix}:${createHash("sha256").update(value).digest("hex").slice(0, 12)}`;
}

function slugify(value, fallback) {
  const slug = (value ?? "")
    .toLowerCase()
    .replace(/[^a-z0-9]+/g, "-")
    .replace(/^-+|-+$/g, "");

  return slug || fallback;
}

function titleFromEmail(email, fallback) {
  if (!email) {
    return fallback;
  }

  const localPart = email.split("@")[0] ?? fallback;
  return localPart
    .split(/[._-]+/)
    .filter(Boolean)
    .map((part) => part.charAt(0).toUpperCase() + part.slice(1))
    .join(" ") || fallback;
}

async function listAuthUsers() {
  const result = await admin.auth().listUsers(20);
  return result.users.filter((user) => user.email);
}

function pickUserByEmail(users, email) {
  if (!email) {
    return null;
  }

  return users.find((user) => user.email?.toLowerCase() === email.toLowerCase()) ?? null;
}

async function syncAccessClaims(user, accessClaim) {
  if (!user?.uid || !accessClaim) {
    return;
  }

  const currentUser = await admin.auth().getUser(user.uid);
  const nextClaims = {
    ...(currentUser.customClaims ?? {}),
    arrzAccess: accessClaim,
  };

  await admin.auth().setCustomUserClaims(user.uid, nextClaims);
  await admin.auth().revokeRefreshTokens(user.uid);
}

async function main() {
  initializeFirebaseAdmin();

  const args = parseArgs(process.argv.slice(2));
  const db = admin.firestore();
  const authUsers = await listAuthUsers();

  const platformEmail = args["platform-email"] ?? authUsers[0]?.email ?? "";
  const merchantEmail = args["merchant-email"] ?? authUsers[1]?.email ?? "";
  const tenantName = args["tenant-name"] ?? "Demo Merchant";
  const tenantSlug = slugify(args["tenant-slug"] ?? tenantName, "demo-merchant");
  const branchCode = args["branch-code"] ?? "demo-branch";
  const branchName = args["branch-name"] ?? "Demo Branch";
  const planName = args["plan-name"] ?? "Demo Rewards";
  const eligibleLabel = args["eligible-label"] ?? "Any drink";
  const thresholdCount = Number.parseInt(args.threshold ?? "5", 10);
  const rewardCreditCount = Number.parseInt(args.reward ?? "1", 10);
  const timezone = args.timezone ?? "Asia/Calcutta";

  if (!platformEmail) {
    throw new Error(
      "No platform admin email was provided and no Firebase Auth users were found. Create a Firebase Auth user first or pass --platform-email.",
    );
  }

  if (!Number.isInteger(thresholdCount) || thresholdCount <= 0) {
    throw new Error("--threshold must be a positive integer.");
  }

  if (!Number.isInteger(rewardCreditCount) || rewardCreditCount <= 0) {
    throw new Error("--reward must be a positive integer.");
  }

  const now = new Date().toISOString();
  const tenantId = `tenant:${tenantSlug}`;
  const branchId = `branch:${tenantSlug}:${slugify(branchCode, "main")}`;
  const planId = `plan:${tenantSlug}:default`;
  const planVersionId = `${planId}:v1`;

  const platformUser = pickUserByEmail(authUsers, platformEmail);
  const merchantUser = pickUserByEmail(authUsers, merchantEmail);

  const platformAdminId = hashId("platform-admin", platformEmail.toLowerCase());
  const merchantStaffId = merchantEmail
    ? hashId("staff", `${tenantId}:${merchantEmail.toLowerCase()}`)
    : null;

  const batch = db.batch();

  batch.set(db.collection("tenants").doc(tenantId), {
    id: tenantId,
    slug: tenantSlug,
    name: tenantName,
    status: "ACTIVE",
    branding: {},
    createdAt: now,
  });

  batch.set(db.collection("branches").doc(branchId), {
    id: branchId,
    tenantId,
    code: branchCode,
    name: branchName,
    timezone,
    address: null,
    status: "ACTIVE",
    createdAt: now,
  });

  batch.set(db.collection("branch_code_lookups").doc(branchCode), {
    id: branchCode,
    branchId,
    tenantId,
    status: "ACTIVE",
    updatedAt: now,
  });

  batch.set(db.collection("plans").doc(planId), {
    id: planId,
    tenantId,
    name: planName,
    eligibleLabel,
    thresholdCount,
    rewardCreditCount,
    currentVersionNumber: 1,
    currentVersionId: planVersionId,
    applicableBranchIds: [branchId],
    status: "ACTIVE",
    validityStartsAt: null,
    validityEndsAt: null,
    redemptionConstraints: {},
    createdAt: now,
    updatedAt: now,
  });

  batch.set(db.collection("plan_versions").doc(planVersionId), {
    id: planVersionId,
    tenantId,
    planId,
    versionNumber: 1,
    name: planName,
    eligibleLabel,
    thresholdCount,
    rewardCreditCount,
    snapshot: {
      applicableBranchIds: [branchId],
      status: "ACTIVE",
    },
    createdAt: now,
  });

  batch.set(db.collection("platform_admin_users").doc(platformAdminId), {
    id: platformAdminId,
    authUserId: platformUser?.uid ?? null,
    status: platformUser ? "ACTIVE" : "INVITED",
    fullName: args["platform-name"] ?? titleFromEmail(platformEmail, "Platform Admin"),
    email: platformEmail,
    emailNormalized: platformEmail.toLowerCase(),
    createdAt: now,
  });

  if (merchantEmail && merchantStaffId) {
    batch.set(db.collection("staff_users").doc(merchantStaffId), {
      id: merchantStaffId,
      tenantId,
      authUserId: merchantUser?.uid ?? null,
      primaryBranchId: branchId,
      role: "MERCHANT_ADMIN",
      status: merchantUser ? "ACTIVE" : "INVITED",
      fullName: args["merchant-name"] ?? titleFromEmail(merchantEmail, "Merchant Admin"),
      email: merchantEmail,
      emailNormalized: merchantEmail.toLowerCase(),
      branchIds: [branchId],
      createdAt: now,
    });

    batch.set(
      db.collection("staff_branch_assignments").doc(`${merchantStaffId}:${branchId}`),
      {
        id: `${merchantStaffId}:${branchId}`,
        tenantId,
        staffUserId: merchantStaffId,
        branchId,
        isPrimary: true,
        createdAt: now,
      },
    );
  }

  await batch.commit();

  await Promise.all([
    syncAccessClaims(
      platformUser,
      platformUser
        ? {
            v: 1,
            actorType: "platform_admin",
            platformAdminUserId: platformAdminId,
            role: "PLATFORM_ADMIN",
            status: "ACTIVE",
            authUserId: platformUser.uid,
          }
        : null,
    ),
    syncAccessClaims(
      merchantUser,
      merchantUser && merchantStaffId
        ? {
            v: 1,
            actorType: "staff",
            staffUserId: merchantStaffId,
            tenantId,
            role: "MERCHANT_ADMIN",
            branchIds: [branchId],
            status: "ACTIVE",
            authUserId: merchantUser.uid,
          }
        : null,
    ),
  ]);

  console.log(
    JSON.stringify(
      {
        ok: true,
        tenantId,
        branchId,
        branchCode,
        planId,
        planVersionId,
        platformAdmin: {
          email: platformEmail,
          uid: platformUser?.uid ?? null,
          docId: platformAdminId,
          status: platformUser ? "ACTIVE" : "INVITED",
        },
        merchantAdmin: merchantEmail
          ? {
              email: merchantEmail,
              uid: merchantUser?.uid ?? null,
              docId: merchantStaffId,
              status: merchantUser ? "ACTIVE" : "INVITED",
            }
          : null,
      },
      null,
      2,
    ),
  );
}

main().catch((error) => {
  console.error(error);
  process.exit(1);
});
