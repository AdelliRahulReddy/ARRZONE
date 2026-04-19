import Link from "next/link";
import { redirect } from "next/navigation";
import { CheckCircle2 } from "lucide-react";
import { StaffSignInPanel } from "@/components/auth/staff-sign-in-panel";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { SetupCallout } from "@/components/setup-callout";
import {
  getAuthIssuePresentation,
  getAuthSurface,
  getAuthSurfaceDefinition,
  getDefaultRedirectForStaffRole,
  sanitizeRedirectTarget,
} from "@/lib/auth/presentation";
import { appEnv } from "@/lib/env";
import { getCurrentPlatformActor, getCurrentStaffActor } from "@/lib/server/auth";
import { AppError } from "@/lib/server/errors";

type SignInPageProps = {
  searchParams?: Promise<{
    redirectTo?: string | string[];
  }>;
};

function firstValue(value: string | string[] | undefined) {
  return Array.isArray(value) ? value[0] : value;
}

function shouldFallbackToPlatform(error: unknown) {
  return (
    error instanceof AppError &&
    error.code === "STAFF_MAPPING_MISSING"
  );
}

export default async function SignInPage({ searchParams }: SignInPageProps) {
  const resolvedSearchParams = searchParams ? await searchParams : undefined;
  const requestedRedirect = sanitizeRedirectTarget(
    firstValue(resolvedSearchParams?.redirectTo),
  );
  const surface = getAuthSurface(requestedRedirect);
  const definition = getAuthSurfaceDefinition(surface);
  const redirectTo = requestedRedirect ?? definition.destination;
  let sessionIssue: ReturnType<typeof getAuthIssuePresentation> | null = null;

  if (!appEnv.hasFirebaseAuth || !appEnv.hasFirebaseAdmin) {
    return (
      <main className="container-edge flex min-h-screen items-center justify-center py-16">
        <div className="w-full max-w-xl space-y-6">
          <SetupCallout
            title="Sign-in is not configured"
            message={`Add the required sign-in keys and secure server credentials to enable ${definition.surfaceLabel.toLowerCase()} access.`}
          />
          <Card className="border-border/70 bg-card/90">
            <CardHeader>
              <CardTitle>Next Step</CardTitle>
            </CardHeader>
            <CardContent className="text-sm leading-6 text-muted-foreground">
              Once sign-in is configured, this route will render the{" "}
              {definition.panelTitle.toLowerCase()} experience. For now, return to the
              overview to continue wiring the app.
              <div className="mt-4">
                <Link href="/" className="text-primary underline underline-offset-4">
                  Back to overview
                </Link>
              </div>
            </CardContent>
          </Card>
        </div>
      </main>
    );
  }

  try {
    if (surface === "platform") {
      const platformActor = await getCurrentPlatformActor();
      if (platformActor) {
        redirect(requestedRedirect ?? "/platform");
      }
    } else {
      try {
        const actor = await getCurrentStaffActor();
        if (actor) {
          redirect(requestedRedirect ?? getDefaultRedirectForStaffRole(actor.role));
        }
      } catch (error) {
        if (!requestedRedirect && shouldFallbackToPlatform(error)) {
          const platformActor = await getCurrentPlatformActor();
          if (platformActor) {
            redirect("/platform");
          }
        } else {
          throw error;
        }
      }
    }
  } catch (error) {
    sessionIssue = getAuthIssuePresentation(error, surface);
  }

  return (
    <main className="container-edge flex min-h-screen items-center justify-center py-16">
      <div className="grid w-full max-w-5xl gap-10 lg:grid-cols-[1.08fr_0.92fr] lg:items-center">
        <section className="space-y-6">
          <p className="text-sm uppercase tracking-[0.24em] text-muted-foreground">
            {definition.eyebrow}
          </p>
          <div className="space-y-3">
            <h1 className="max-w-3xl text-4xl font-semibold tracking-tight text-balance sm:text-5xl">
              {definition.heroTitle}
            </h1>
            <p className="max-w-2xl text-lg leading-8 text-muted-foreground">
              {definition.heroDescription}
            </p>
          </div>
          <div className="grid gap-3 sm:grid-cols-3">
            {definition.requirements.map((requirement) => (
              <div
                key={requirement}
                className="rounded-2xl border border-border/70 bg-card/75 px-4 py-4 shadow-sm"
              >
                <div className="flex items-start gap-3">
                  <CheckCircle2
                    aria-hidden="true"
                    className="mt-0.5 size-4 shrink-0 text-primary"
                  />
                  <p className="text-sm leading-6 text-muted-foreground">
                    {requirement}
                  </p>
                </div>
              </div>
            ))}
          </div>
          <div className="inline-flex items-center gap-2 rounded-full border border-border/70 bg-background/80 px-4 py-2 text-sm text-muted-foreground">
            Destination
            <span className="font-mono text-foreground" translate="no">
              {redirectTo}
            </span>
          </div>
        </section>
        <StaffSignInPanel
          surface={surface}
          redirectTo={redirectTo}
          sessionIssue={sessionIssue}
        />
      </div>
    </main>
  );
}
