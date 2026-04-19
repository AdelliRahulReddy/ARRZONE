// @vitest-environment node

import { beforeEach, describe, expect, it, vi } from "vitest";
import { AppError } from "@/lib/server/errors";

const {
  createStaffSessionCookieMock,
  verifyStaffIdTokenMock,
  resolveStaffActorFromClaimsMock,
  resolvePlatformActorFromClaimsMock,
  assertStaffActorRoleMock,
} = vi.hoisted(() => ({
  createStaffSessionCookieMock: vi.fn(),
  verifyStaffIdTokenMock: vi.fn(),
  resolveStaffActorFromClaimsMock: vi.fn(),
  resolvePlatformActorFromClaimsMock: vi.fn(),
  assertStaffActorRoleMock: vi.fn(),
}));

vi.mock("@/lib/server/firebase-auth", () => ({
  createStaffSessionCookie: createStaffSessionCookieMock,
  verifyStaffIdToken: verifyStaffIdTokenMock,
}));

vi.mock("@/lib/server/auth", () => ({
  resolveStaffActorFromClaims: resolveStaffActorFromClaimsMock,
  resolvePlatformActorFromClaims: resolvePlatformActorFromClaimsMock,
  assertStaffActorRole: assertStaffActorRoleMock,
}));

import { STAFF_SESSION_COOKIE_NAME } from "@/lib/auth/constants";
import { POST as createSession } from "@/app/api/auth/session/route";
import { POST as clearSession } from "@/app/api/auth/logout/route";

describe("auth session routes", () => {
  beforeEach(() => {
    vi.clearAllMocks();
    verifyStaffIdTokenMock.mockResolvedValue({
      uid: "firebase-uid-1",
      email: "staff@example.com",
      email_verified: true,
    });
    resolveStaffActorFromClaimsMock.mockResolvedValue({
      role: "MERCHANT_ADMIN",
    });
    resolvePlatformActorFromClaimsMock.mockResolvedValue({
      role: "PLATFORM_ADMIN",
    });
  });

  it("uses platform admin resolution for the platform surface", async () => {
    createStaffSessionCookieMock.mockResolvedValue("session-cookie-value");

    const response = await createSession(
      new Request("http://localhost/api/auth/session", {
        method: "POST",
        body: JSON.stringify({
          idToken: "firebase-id-token",
          redirectTo: "/platform",
        }),
      }),
    );

    expect(response.status).toBe(200);
    expect(verifyStaffIdTokenMock).toHaveBeenCalledWith("firebase-id-token");
    expect(resolvePlatformActorFromClaimsMock).toHaveBeenCalled();
    expect(resolveStaffActorFromClaimsMock).not.toHaveBeenCalled();
    expect(assertStaffActorRoleMock).not.toHaveBeenCalled();
    expect(createStaffSessionCookieMock).toHaveBeenCalledWith("firebase-id-token");
    expect(response.headers.get("set-cookie")).toContain(
      `${STAFF_SESSION_COOKIE_NAME}=session-cookie-value`,
    );
    await expect(response.json()).resolves.toMatchObject({
      ok: true,
      data: {
        redirectTo: "/platform",
      },
    });
  });

  it("falls back to a staff role-based redirect when no surface is requested", async () => {
    createStaffSessionCookieMock.mockResolvedValue("session-cookie-value");

    const response = await createSession(
      new Request("http://localhost/api/auth/session", {
        method: "POST",
        body: JSON.stringify({ idToken: "firebase-id-token" }),
      }),
    );

    expect(assertStaffActorRoleMock).not.toHaveBeenCalled();
    await expect(response.json()).resolves.toMatchObject({
      ok: true,
      data: {
        redirectTo: "/business-admin",
      },
    });
  });

  it("falls back to platform resolution when staff mapping is missing on generic sign-in", async () => {
    createStaffSessionCookieMock.mockResolvedValue("session-cookie-value");
    resolveStaffActorFromClaimsMock.mockRejectedValue(
      new AppError("No staff record matched this Firebase account.", 403, "STAFF_MAPPING_MISSING"),
    );

    const response = await createSession(
      new Request("http://localhost/api/auth/session", {
        method: "POST",
        body: JSON.stringify({ idToken: "firebase-id-token" }),
      }),
    );

    expect(resolvePlatformActorFromClaimsMock).toHaveBeenCalled();
    await expect(response.json()).resolves.toMatchObject({
      ok: true,
      data: {
        redirectTo: "/platform",
      },
    });
  });

  it("canonicalizes legacy merchant redirects to the business-admin route", async () => {
    createStaffSessionCookieMock.mockResolvedValue("session-cookie-value");

    const response = await createSession(
      new Request("http://localhost/api/auth/session", {
        method: "POST",
        body: JSON.stringify({
          idToken: "firebase-id-token",
          redirectTo: "/merchant",
        }),
      }),
    );

    await expect(response.json()).resolves.toMatchObject({
      ok: true,
      data: {
        redirectTo: "/business-admin",
      },
    });
  });

  it("clears the staff session cookie on logout", async () => {
    const response = await clearSession();

    expect(response.status).toBe(200);
    expect(response.headers.get("set-cookie")).toContain(
      `${STAFF_SESSION_COOKIE_NAME}=;`,
    );
    expect(response.headers.get("set-cookie")).toContain("Max-Age=0");
  });
});
