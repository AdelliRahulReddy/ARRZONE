"use client";

import { useState } from "react";
import { usePathname } from "next/navigation";
import { LogOut } from "lucide-react";
import { getFirebaseClientAuth } from "@/lib/firebase/client";
import { buildSignInHref } from "@/lib/auth/presentation";
import { Button } from "@/components/ui/button";

export function SignOutButton() {
  const pathname = usePathname();
  const [pending, setPending] = useState(false);

  async function handleSignOut() {
    setPending(true);

    try {
      const auth = await getFirebaseClientAuth();
      await auth.signOut();
    } catch {
      // Ignore client sign-out failures and still clear the server cookie.
    }

    try {
      await fetch("/api/auth/logout", {
        method: "POST",
      });
    } finally {
      const nextHref =
        pathname && pathname !== "/sign-in"
          ? buildSignInHref(pathname)
          : "/sign-in";
      window.location.assign(nextHref);
    }
  }

  return (
    <Button
      type="button"
      variant="outline"
      className="rounded-full"
      onClick={handleSignOut}
      disabled={pending}
    >
      <LogOut />
      {pending ? "Signing out…" : "Sign out"}
    </Button>
  );
}
