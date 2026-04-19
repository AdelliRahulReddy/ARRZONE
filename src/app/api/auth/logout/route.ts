import { jsonOk } from "@/lib/server/api";
import { STAFF_SESSION_COOKIE_NAME } from "@/lib/auth/constants";

export async function POST() {
  const response = jsonOk({ sessionCleared: true });
  response.cookies.set(STAFF_SESSION_COOKIE_NAME, "", {
    httpOnly: true,
    maxAge: 0,
    path: "/",
    sameSite: "lax",
    secure: process.env.NODE_ENV === "production",
  });
  return response;
}
