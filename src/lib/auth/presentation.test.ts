import { describe, expect, it } from "vitest";
import {
  getAuthSurface,
  getDefaultRedirectForStaffRole,
  sanitizeRedirectTarget,
} from "@/lib/auth/presentation";

describe("auth presentation helpers", () => {
  it("sanitizes valid internal redirects", () => {
    expect(sanitizeRedirectTarget("/platform?tab=alerts")).toBe(
      "/platform?tab=alerts",
    );
    expect(sanitizeRedirectTarget("/merchant?tab=staff")).toBe(
      "/business-admin?tab=staff",
    );
  });

  it("rejects external or malformed redirects", () => {
    expect(sanitizeRedirectTarget("https://example.com")).toBeNull();
    expect(sanitizeRedirectTarget("//example.com")).toBeNull();
    expect(sanitizeRedirectTarget("platform")).toBeNull();
  });

  it("maps redirect targets to auth surfaces", () => {
    expect(getAuthSurface("/platform")).toBe("platform");
    expect(getAuthSurface("/business-admin/reports")).toBe("merchant");
    expect(getAuthSurface("/merchant/reports")).toBe("merchant");
    expect(getAuthSurface("/staff")).toBe("staff");
  });

  it("chooses a sensible default redirect for each role", () => {
    expect(getDefaultRedirectForStaffRole("MERCHANT_ADMIN")).toBe("/business-admin");
    expect(getDefaultRedirectForStaffRole("MANAGER")).toBe("/staff");
  });
});
