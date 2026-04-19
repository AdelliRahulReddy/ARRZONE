import { createHash, randomBytes } from "node:crypto";
import { addSeconds, isAfter } from "date-fns";
import { REDEEM_TOKEN_TTL_SECONDS } from "@/lib/constants";
import { appEnv, requireConfiguredValue } from "@/lib/env";

export type RedeemTokenRecord = {
  rawToken: string;
  tokenHash: string;
  expiresAt: Date;
  preview: string;
};

export type RedeemTokenLifecycle = {
  expiresAt: Date;
  consumedAt?: Date | null;
  revokedAt?: Date | null;
};

function getPepper() {
  return requireConfiguredValue(
    appEnv.redeemTokenPepper,
    "REDEEM_TOKEN_PEPPER",
  );
}

export function createRedeemTokenRecord(): RedeemTokenRecord {
  const rawToken = randomBytes(24).toString("base64url");
  return {
    rawToken,
    tokenHash: hashRedeemToken(rawToken),
    expiresAt: addSeconds(new Date(), REDEEM_TOKEN_TTL_SECONDS),
    preview: rawToken.slice(-6),
  };
}

export function hashRedeemToken(token: string) {
  return createHash("sha256").update(`${token}:${getPepper()}`).digest("hex");
}

export function isRedeemTokenExpired(expiresAt: Date) {
  return isAfter(new Date(), expiresAt);
}

export function getRedeemTokenDisposition(input: RedeemTokenLifecycle) {
  if (input.revokedAt) {
    return "revoked" as const;
  }

  if (input.consumedAt) {
    return "used" as const;
  }

  if (isRedeemTokenExpired(input.expiresAt)) {
    return "expired" as const;
  }

  return "valid" as const;
}
