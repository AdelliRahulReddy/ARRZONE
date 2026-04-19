import { NextResponse } from "next/server";
import type { NextRequest } from "next/server";
import {
  BUSINESS_ADMIN_ROUTE,
  LEGACY_BUSINESS_ADMIN_ROUTE,
  STAFF_SESSION_COOKIE_NAME,
} from "@/lib/auth/constants";

const protectedPagePrefixes = ["/staff", BUSINESS_ADMIN_ROUTE, LEGACY_BUSINESS_ADMIN_ROUTE, "/platform"];
const protectedApiPrefixes = [
  "/api/v1/memberships",
  "/api/v1/plans",
  "/api/v1/branches",
  "/api/v1/staff-users",
  "/api/v1/business-admin-users",
  "/api/v1/tenants",
  "/api/v1/platform-admin-users",
  "/api/v1/reports",
  "/api/v1/redemptions",
];

function isProtectedPrefix(pathname: string, prefixes: string[]) {
  return prefixes.some((prefix) => pathname === prefix || pathname.startsWith(`${prefix}/`));
}

export default function proxy(request: NextRequest) {
  const { pathname, search } = request.nextUrl;
  const hasSessionCookie = Boolean(
    request.cookies.get(STAFF_SESSION_COOKIE_NAME)?.value,
  );

  if (hasSessionCookie) {
    return NextResponse.next();
  }

  if (isProtectedPrefix(pathname, protectedApiPrefixes)) {
    return NextResponse.json(
      {
        ok: false,
        error: {
          code: "UNAUTHORIZED",
          message: "Authentication is required.",
        },
      },
      { status: 401 },
    );
  }

  if (isProtectedPrefix(pathname, protectedPagePrefixes)) {
    const signInUrl = new URL("/sign-in", request.url);
    signInUrl.searchParams.set("redirectTo", `${pathname}${search}`);
    return NextResponse.redirect(signInUrl);
  }

  return NextResponse.next();
}

export const config = {
  matcher: [
    "/((?!_next|[^?]*\\.(?:html?|css|js(?!on)|png|svg|ico|txt|xml|map)).*)",
    "/(api|trpc)(.*)",
  ],
};
