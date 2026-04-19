import "server-only";
import { createHash, randomUUID } from "node:crypto";
import { subSeconds } from "date-fns";
import { DEFAULT_RATE_LIMITS, SECURITY_EVENT_TYPES } from "@/lib/constants";
import {
  COLLECTIONS,
  nowIso,
  type SecurityEventDoc,
} from "@/lib/firebase/model";
import type { AppDatabase } from "@/lib/server/db";
import { AppError } from "@/lib/server/errors";

export type SecurityContext = {
  tenantId?: string | null;
  branchId?: string | null;
  staffUserId?: string | null;
  ip?: string | null;
};

export function hashIpAddress(ip?: string | null) {
  if (!ip) {
    return null;
  }

  return createHash("sha256").update(ip).digest("hex");
}

export async function recordSecurityEvent(
  db: AppDatabase,
  input: {
    eventType: string;
    scopeKey: string;
    subjectKey?: string | null;
    metadata?: Record<string, unknown>;
  } & SecurityContext,
) {
  const event: SecurityEventDoc = {
    id: randomUUID(),
    tenantId: input.tenantId ?? null,
    branchId: input.branchId ?? null,
    staffUserId: input.staffUserId ?? null,
    eventType: input.eventType,
    scopeKey: input.scopeKey,
    subjectKey: input.subjectKey ?? null,
    ipHash: hashIpAddress(input.ip),
    metadata: input.metadata ?? {},
    createdAt: nowIso(),
  };

  await db.collection(COLLECTIONS.securityEvents).doc(event.id).set(event);
}

export async function assertRateLimit(
  db: AppDatabase,
  key:
    | keyof typeof DEFAULT_RATE_LIMITS
    | { name: string; limit: number; windowSeconds: number },
  scope: {
    eventType: string;
    scopeKey: string;
    subjectKey?: string | null;
  } & SecurityContext,
) {
  const config = typeof key === "string" ? DEFAULT_RATE_LIMITS[key] : key;
  const createdAfter = nowIso(subSeconds(new Date(), config.windowSeconds));
  const attempts = await db
    .collection(COLLECTIONS.securityEvents)
    .where("eventType", "==", scope.eventType)
    .where("scopeKey", "==", scope.scopeKey)
    .where("createdAt", ">=", createdAfter)
    .get();

  await recordSecurityEvent(db, scope);

  if (attempts.size >= config.limit) {
    await recordSecurityEvent(db, {
      ...scope,
      eventType: SECURITY_EVENT_TYPES.rateLimitExceeded,
      metadata: {
        limitedEventType: scope.eventType,
        limit: config.limit,
        windowSeconds: config.windowSeconds,
      },
    });

    throw new AppError(
      "Too many requests. Try again shortly.",
      429,
      "RATE_LIMITED",
    );
  }
}
