import { SignOutButton } from "@/components/auth/sign-out-button";
import { PlatformAdminDashboard } from "@/components/platform/platform-admin-dashboard";
import { SurfaceStateCard } from "@/components/auth/surface-state-card";
import { DashboardHero } from "@/components/admin/dashboard-primitives";
import { SetupCallout } from "@/components/setup-callout";
import {
  getAuthIssuePresentation,
  getOperationIssueMessage,
} from "@/lib/auth/presentation";
import { appEnv } from "@/lib/env";
import { requirePlatformActor } from "@/lib/server/auth";
import { AppError } from "@/lib/server/errors";
import {
  getPlatformExceptionsReport,
  getPlatformOverviewReport,
  listBusinessAdminDirectory,
  listPlatformAdminUsers,
  listTenantDirectory,
} from "@/lib/server/loyalty-service";

export default async function PlatformPage() {
  if (!appEnv.hasFirebaseAuth || !appEnv.hasFirebaseAdmin) {
    return (
      <main className="container-edge py-10">
        <SetupCallout
          title="Platform admin sign-in is not configured"
          message="Finish sign-in setup and invite an approved platform admin to review cross-business health."
          actionHref="/sign-in?redirectTo=%2Fplatform"
          actionLabel="Open sign-in"
        />
      </main>
    );
  }

  let actor:
    | Awaited<ReturnType<typeof requirePlatformActor>>
    | null = null;
  let accessIssue: ReturnType<typeof getAuthIssuePresentation> | null = null;
  let dataIssueMessage: string | null = null;
  let overview:
    | Awaited<ReturnType<typeof getPlatformOverviewReport>>
    | null = null;
  let tenants:
    | Awaited<ReturnType<typeof listTenantDirectory>>
    | null = null;
  let platformAdmins:
    | Awaited<ReturnType<typeof listPlatformAdminUsers>>
    | null = null;
  let businessAdmins:
    | Awaited<ReturnType<typeof listBusinessAdminDirectory>>
    | null = null;
  let exceptions:
    | Awaited<ReturnType<typeof getPlatformExceptionsReport>>
    | null = null;

  try {
    actor = await requirePlatformActor();
  } catch (error) {
    accessIssue = getAuthIssuePresentation(error, "platform");
  }

  if (actor) {
    try {
      [overview, tenants, platformAdmins, businessAdmins, exceptions] = await Promise.all([
        getPlatformOverviewReport(),
        listTenantDirectory(),
        listPlatformAdminUsers(),
        listBusinessAdminDirectory(),
        getPlatformExceptionsReport(),
      ]);
    } catch (error) {
      dataIssueMessage = getOperationIssueMessage(
        error,
        "The platform alerts could not be loaded right now. Refresh the page and try again.",
      );
    }
  }

  if (!actor) {
    const issue =
      accessIssue ??
      getAuthIssuePresentation(
        new AppError("Unauthorized.", 401, "UNAUTHORIZED"),
        "platform",
      );

    return (
      <main className="container-edge flex min-h-screen items-center justify-center py-16">
        <SurfaceStateCard
          surface="platform"
          title={issue.title}
          message={issue.message}
          primaryActionHref="/sign-in?redirectTo=%2Fplatform"
          primaryActionLabel="Open Platform Admin Sign-In"
          showSignOut={issue.showSignOut}
        />
      </main>
    );
  }

  if (!overview || !tenants || !platformAdmins || !businessAdmins || !exceptions) {
    return (
      <main className="container-edge flex min-h-screen items-center justify-center py-16">
        <SurfaceStateCard
          surface="platform"
          title="Platform Console Unavailable"
          message={
            dataIssueMessage ??
            "The platform alerts could not be loaded right now. Refresh the page and try again."
          }
          primaryActionHref="/platform"
          primaryActionLabel="Retry Platform Console"
          showSignOut
        />
      </main>
    );
  }

  return (
    <main className="container-edge min-h-screen space-y-8 py-6 sm:py-10">
      <DashboardHero
        eyebrow="Platform admin"
        title="Platform operations dashboard"
        description="Provision tenants, manage elevated access, and review cross-business security posture from a single control plane."
        actions={<SignOutButton />}
        stats={[
          {
            label: "Tenants",
            value: overview.totalTenants,
          },
          {
            label: "Branches",
            value: overview.totalBranches,
          },
          {
            label: "Platform admins",
            value: overview.totalPlatformAdmins,
          },
          {
            label: "Security events",
            value: overview.totalSecurityEvents,
          },
        ]}
      />
      <PlatformAdminDashboard
        overview={overview}
        tenants={tenants}
        platformAdmins={platformAdmins}
        businessAdmins={businessAdmins}
        exceptions={exceptions}
      />
    </main>
  );
}
