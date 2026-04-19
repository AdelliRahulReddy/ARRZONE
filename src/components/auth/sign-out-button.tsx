"use client";

import { startTransition, useState } from "react";
import { usePathname, useRouter } from "next/navigation";
import { LogOut } from "lucide-react";
import { getFirebaseClientAuth } from "@/lib/firebase/client";
import { buildSignInHref } from "@/lib/auth/presentation";
import { Button } from "@/components/ui/button";

export function SignOutButton() {
  const router = useRouter();
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
      startTransition(() => {
        router.replace(nextHref);
        router.refresh();
      });
      setPending(false);
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
