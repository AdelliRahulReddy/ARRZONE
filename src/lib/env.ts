const DEFAULT_DEV_PASS_SECRET = "dev-pass-secret-change-me";
const DEFAULT_DEV_REDEEM_PEPPER = "dev-redeem-pepper-change-me";
const normalizeEnv = (value?: string | null) => value?.trim() || "";
const normalizePrivateKey = (value?: string | null) =>
  normalizeEnv(value).replace(/\\n/g, "\n");

const firebaseProjectId = normalizeEnv(process.env.FIREBASE_PROJECT_ID);
const firebaseClientEmail = normalizeEnv(process.env.FIREBASE_CLIENT_EMAIL);
const firebasePrivateKey = normalizePrivateKey(process.env.FIREBASE_PRIVATE_KEY);
const firebaseApiKey = normalizeEnv(process.env.NEXT_PUBLIC_FIREBASE_API_KEY);
const firebaseAuthDomain = normalizeEnv(process.env.NEXT_PUBLIC_FIREBASE_AUTH_DOMAIN);
const firebaseAppId = normalizeEnv(process.env.NEXT_PUBLIC_FIREBASE_APP_ID);
const firebaseWebProjectId = normalizeEnv(
  process.env.NEXT_PUBLIC_FIREBASE_PROJECT_ID,
);
const hasFirebaseCredentials = Boolean(
  firebaseProjectId &&
    (process.env.FIRESTORE_EMULATOR_HOST ||
      process.env.GOOGLE_APPLICATION_CREDENTIALS ||
      (firebaseClientEmail && firebasePrivateKey)),
);

export const appEnv = {
  appUrl: process.env.NEXT_PUBLIC_APP_URL ?? "http://localhost:3000",
  firebaseProjectId,
  firebaseClientEmail,
  firebasePrivateKey,
  firebaseApiKey,
  firebaseAuthDomain,
  firebaseAppId,
  firebaseWebProjectId,
  defaultPhoneCountryCode:
    process.env.DEFAULT_PHONE_COUNTRY_CODE?.trim() || "+91",
  passTokenSecret:
    process.env.PASS_TOKEN_SECRET ||
    (process.env.NODE_ENV === "production" ? "" : DEFAULT_DEV_PASS_SECRET),
  redeemTokenPepper:
    process.env.REDEEM_TOKEN_PEPPER ||
    (process.env.NODE_ENV === "production" ? "" : DEFAULT_DEV_REDEEM_PEPPER),
  hasFirebaseAuth: Boolean(
    firebaseApiKey && firebaseAuthDomain && firebaseWebProjectId && firebaseAppId,
  ),
  hasFirebaseAdmin: hasFirebaseCredentials,
} as const;

export function requireConfiguredValue(value: string, name: string) {
  if (!value) {
    throw new Error(`${name} is not configured.`);
  }

  return value;
}
