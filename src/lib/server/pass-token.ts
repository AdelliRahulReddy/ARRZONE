import { SignJWT, jwtVerify } from "jose";
import { appEnv, requireConfiguredValue } from "@/lib/env";

export type PassTokenClaims = {
  type: "PASS";
  passId: string;
  membershipId: string;
  tenantId: string;
  passVersion: number;
};

function getPassSecret() {
  return new TextEncoder().encode(
    requireConfiguredValue(appEnv.passTokenSecret, "PASS_TOKEN_SECRET"),
  );
}

export async function signPassToken(claims: PassTokenClaims) {
  return new SignJWT(claims)
    .setProtectedHeader({ alg: "HS256", typ: "JWT" })
    .setIssuedAt()
    .setExpirationTime("365d")
    .sign(getPassSecret());
}

export async function verifyPassToken(token: string) {
  const { payload } = await jwtVerify(token, getPassSecret());

  return {
    type: "PASS" as const,
    passId: String(payload.passId),
    membershipId: String(payload.membershipId),
    tenantId: String(payload.tenantId),
    passVersion: Number(payload.passVersion),
  };
}
