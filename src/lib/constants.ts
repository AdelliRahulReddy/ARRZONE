export const REDEEM_TOKEN_TTL_SECONDS = 60;
export const RAPID_PURCHASE_WINDOW_SECONDS = 120;
export const DEFAULT_RATE_LIMITS = {
  phoneSearch: { limit: 10, windowSeconds: 60 },
  passLookup: { limit: 30, windowSeconds: 60 },
  qrLookup: { limit: 30, windowSeconds: 60 },
  redeemTokenGeneration: { limit: 3, windowSeconds: 60 },
  redeemConsume: { limit: 10, windowSeconds: 60 },
} as const;

export const SECURITY_EVENT_TYPES = {
  phoneSearchAttempt: "PHONE_SEARCH_ATTEMPT",
  passLookupAttempt: "PASS_LOOKUP_ATTEMPT",
  qrLookupAttempt: "QR_LOOKUP_ATTEMPT",
  redeemTokenGenerationAttempt: "REDEEM_TOKEN_GENERATION_ATTEMPT",
  redeemConsumeAttempt: "REDEEM_CONSUME_ATTEMPT",
  rateLimitExceeded: "RATE_LIMIT_EXCEEDED",
  suspiciousActivity: "SUSPICIOUS_ACTIVITY",
} as const;
