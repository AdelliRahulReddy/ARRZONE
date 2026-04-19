// @vitest-environment node

import { beforeEach, describe, expect, it, vi } from "vitest";

const {
  cookiesMock,
  headersMock,
  getAuthMock,
  verifySessionCookieMock,
  createSessionCookieMock,
  verifyIdTokenMock,
} = vi.hoisted(() => ({
  cookiesMock: vi.fn(),
  headersMock: vi.fn(),
  getAuthMock: vi.fn(),
  verifySessionCookieMock: vi.fn(),
  createSessionCookieMock: vi.fn(),
  verifyIdTokenMock: vi.fn(),
}));

vi.mock("next/headers", () => ({
  cookies: cookiesMock,
  headers: headersMock,
}));

vi.mock("server-only", () => ({}));

vi.mock("firebase-admin/app", () => ({
  getApps: vi.fn(() => [{}]),
}));

vi.mock("firebase-admin/auth", () => ({
  getAuth: getAuthMock,
}));

vi.mock("@/lib/env", () => ({
  appEnv: {
    hasFirebaseAdmin: true,
  },
}));

vi.mock("@/lib/firebase/admin", () => ({
  getFirestoreAdmin: vi.fn(),
}));

import {
  createStaffSessionCookie,
  getCurrentStaffSessionClaims,
  verifyStaffIdToken,
} from "@/lib/server/firebase-auth";

describe("firebase auth session helpers", () => {
  beforeEach(() => {
    vi.clearAllMocks();
    headersMock.mockResolvedValue({
      get: vi.fn(() => undefined),
    });
    getAuthMock.mockReturnValue({
      verifySessionCookie: verifySessionCookieMock,
      createSessionCookie: createSessionCookieMock,
      verifyIdToken: verifyIdTokenMock,
    });
  });

  it("returns null when no staff session cookie is present", async () => {
    cookiesMock.mockResolvedValue({
      get: vi.fn(() => undefined),
    });

    await expect(getCurrentStaffSessionClaims()).resolves.toBeNull();
    expect(verifySessionCookieMock).not.toHaveBeenCalled();
  });

  it("returns null when the staff session cookie is invalid or revoked", async () => {
    cookiesMock.mockResolvedValue({
      get: vi.fn(() => ({ value: "bad-cookie" })),
    });
    verifySessionCookieMock.mockRejectedValue(new Error("revoked"));

    await expect(getCurrentStaffSessionClaims()).resolves.toBeNull();
    expect(verifySessionCookieMock).toHaveBeenCalledWith("bad-cookie", true);
  });

  it("returns decoded session claims for a valid staff session cookie", async () => {
    cookiesMock.mockResolvedValue({
      get: vi.fn(() => ({ value: "good-cookie" })),
    });
    verifySessionCookieMock.mockResolvedValue({
      uid: "firebase-uid-1",
      email: "staff@example.com",
      email_verified: true,
    });

    await expect(getCurrentStaffSessionClaims()).resolves.toMatchObject({
      uid: "firebase-uid-1",
      email: "staff@example.com",
    });
  });

  it("prefers a Firebase bearer token when the request carries Authorization", async () => {
    verifyIdTokenMock.mockResolvedValue({
      uid: "firebase-uid-2",
      email: "staff@example.com",
      email_verified: true,
    });

    await expect(
      getCurrentStaffSessionClaims({
        request: {
          headers: new Headers({
            authorization: "Bearer mobile-id-token",
          }),
        } as Request,
      }),
    ).resolves.toMatchObject({
      uid: "firebase-uid-2",
    });

    expect(verifyIdTokenMock).toHaveBeenCalledWith("mobile-id-token", true);
    expect(verifySessionCookieMock).not.toHaveBeenCalled();
  });

  it("creates a Firebase session cookie for a valid id token", async () => {
    createSessionCookieMock.mockResolvedValue("session-cookie");

    await expect(createStaffSessionCookie("id-token")).resolves.toBe("session-cookie");
    expect(createSessionCookieMock).toHaveBeenCalledWith(
      "id-token",
      expect.objectContaining({
        expiresIn: 432000000,
      }),
    );
  });

  it("verifies a Firebase id token before issuing a session cookie", async () => {
    verifyIdTokenMock.mockResolvedValue({
      uid: "firebase-uid-1",
      email: "staff@example.com",
      email_verified: true,
    });

    await expect(verifyStaffIdToken("id-token")).resolves.toMatchObject({
      uid: "firebase-uid-1",
    });
    expect(verifyIdTokenMock).toHaveBeenCalledWith("id-token", true);
  });
});
