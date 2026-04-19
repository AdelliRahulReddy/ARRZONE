import { describe, expect, it } from "vitest";
import { buildRedeemQrPayload, parseScanPayload } from "@/lib/qr";

describe("QR payload parsing", () => {
  it("classifies pass links separately from redeem tokens", () => {
    expect(
      parseScanPayload("https://example.com/pass/eyJhbGciOiJIUzI1NiJ9"),
    ).toMatchObject({
      type: "PASS",
      token: "eyJhbGciOiJIUzI1NiJ9",
    });

    expect(parseScanPayload(buildRedeemQrPayload("opaque-token"))).toEqual({
      type: "REDEEM",
      raw: "LOYALTY_REDEEM:opaque-token",
      token: "opaque-token",
    });
  });

  it("treats a pass screenshot as non-redeemable until a live redeem token exists", () => {
    const staticPass = parseScanPayload("https://example.com/pass/static-pass-token");
    expect(staticPass.type).toBe("PASS");

    const redeemPayload = parseScanPayload(buildRedeemQrPayload("live-token"));
    expect(redeemPayload.type).toBe("REDEEM");
    if (redeemPayload.type !== "REDEEM") {
      throw new Error("Expected a redeem payload.");
    }

    expect(redeemPayload.token).toBe("live-token");
  });
});
