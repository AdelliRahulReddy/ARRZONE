// @vitest-environment node

import { describe, expect, it } from "vitest";
import { NextRequest } from "next/server";
import proxy from "@/proxy";

describe("proxy auth gate", () => {
  it("redirects protected pages to /sign-in when no session cookie is present", () => {
    const response = proxy(new NextRequest("http://localhost/staff"));

    expect(response.status).toBe(307);
    expect(response.headers.get("location")).toBe(
      "http://localhost/sign-in?redirectTo=%2Fstaff",
    );
  });

  it("protects the business-admin route when no session cookie is present", () => {
    const response = proxy(new NextRequest("http://localhost/business-admin"));

    expect(response.status).toBe(307);
    expect(response.headers.get("location")).toBe(
      "http://localhost/sign-in?redirectTo=%2Fbusiness-admin",
    );
  });

  it("returns 401 for protected APIs when no session cookie is present", async () => {
    const response = proxy(
      new NextRequest("http://localhost/api/v1/memberships/search"),
    );

    expect(response.status).toBe(401);
    await expect(response.json()).resolves.toMatchObject({
      ok: false,
      error: {
        code: "UNAUTHORIZED",
      },
    });
  });

  it("allows protected requests through when the staff session cookie is present", () => {
    const response = proxy(
      new NextRequest("http://localhost/staff", {
        headers: {
          cookie: "staff_session=active-cookie",
        },
      }),
    );

    expect(response.status).toBe(200);
  });
});
