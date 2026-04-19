import { describe, expect, it, vi } from "vitest";
import {
  createRedeemTokenRecord,
  getRedeemTokenDisposition,
  hashRedeemToken,
} from "@/lib/server/redeem-token";

describe("redeem token lifecycle", () => {
  it("issues unique short-lived opaque tokens", () => {
    const first = createRedeemTokenRecord();
    const second = createRedeemTokenRecord();

    expect(first.rawToken).not.toBe(second.rawToken);
    expect(first.preview).toHaveLength(6);
    expect(first.expiresAt.getTime()).toBeGreaterThan(Date.now());
  });

  it("hashes the same token deterministically", () => {
    expect(hashRedeemToken("opaque-token")).toBe(hashRedeemToken("opaque-token"));
  });

  it("reports valid, expired, used, and revoked states", () => {
    vi.useFakeTimers();
    vi.setSystemTime(new Date("2026-01-01T10:00:00.000Z"));

    const expiresAt = new Date("2026-01-01T10:01:00.000Z");

    expect(getRedeemTokenDisposition({ expiresAt })).toBe("valid");
    expect(
      getRedeemTokenDisposition({
        expiresAt,
        consumedAt: new Date("2026-01-01T10:00:10.000Z"),
      }),
    ).toBe("used");
    expect(
      getRedeemTokenDisposition({
        expiresAt,
        revokedAt: new Date("2026-01-01T10:00:10.000Z"),
      }),
    ).toBe("revoked");

    vi.setSystemTime(new Date("2026-01-01T10:02:00.000Z"));
    expect(getRedeemTokenDisposition({ expiresAt })).toBe("expired");
    vi.useRealTimers();
  });
});
