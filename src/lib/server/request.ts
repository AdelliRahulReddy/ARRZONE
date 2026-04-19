import { AppError } from "@/lib/server/errors";

export function getRequestIp(request: Request) {
  const forwarded = request.headers.get("x-forwarded-for");
  if (forwarded) {
    return forwarded.split(",")[0]?.trim() ?? null;
  }

  return request.headers.get("x-real-ip");
}

export function requireIdempotencyKey(request: Request) {
  const idempotencyKey = request.headers.get("Idempotency-Key");
  if (!idempotencyKey) {
    throw new AppError(
      "Idempotency-Key header is required for write operations.",
      400,
      "IDEMPOTENCY_KEY_REQUIRED",
    );
  }

  return idempotencyKey;
}
