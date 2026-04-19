import Link from "next/link";
import { AlertTriangle } from "lucide-react";
import { SignOutButton } from "@/components/auth/sign-out-button";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import {
  buildSignInHref,
  getAuthSurfaceDefinition,
  type AuthSurface,
} from "@/lib/auth/presentation";

type SurfaceStateCardProps = {
  surface: AuthSurface;
  title: string;
  message: string;
  primaryActionHref?: string;
  primaryActionLabel?: string;
  showSignOut?: boolean;
};

export function SurfaceStateCard({
  surface,
  title,
  message,
  primaryActionHref,
  primaryActionLabel,
  showSignOut = false,
}: SurfaceStateCardProps) {
  const definition = getAuthSurfaceDefinition(surface);
  const actionHref =
    primaryActionHref ?? buildSignInHref(definition.destination);
  const actionLabel = primaryActionLabel ?? `Open ${definition.panelTitle}`;

  return (
    <Card className="w-full max-w-xl border-border/70 bg-card/92 shadow-xl shadow-black/5">
      <CardHeader className="space-y-3">
        <div className="inline-flex w-fit items-center gap-2 rounded-full border border-border/60 bg-background/80 px-3 py-1 text-xs uppercase tracking-[0.24em] text-muted-foreground">
          <AlertTriangle aria-hidden="true" className="size-3.5" />
          {definition.eyebrow}
        </div>
        <CardTitle className="text-2xl tracking-tight text-balance">
          {title}
        </CardTitle>
      </CardHeader>
      <CardContent className="space-y-5">
        <p className="text-sm leading-6 text-muted-foreground break-words">
          {message}
        </p>
        <div className="flex flex-col gap-3 sm:flex-row">
          <Button asChild className="rounded-full">
            <Link href={actionHref}>{actionLabel}</Link>
          </Button>
          {showSignOut ? <SignOutButton /> : null}
        </div>
      </CardContent>
    </Card>
  );
}
