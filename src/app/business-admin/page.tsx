import { SignOutButton } from "@/components/auth/sign-out-button";
import { MerchantAdminDashboard } from "@/components/merchant/merchant-admin-dashboard";
import { SurfaceStateCard } from "@/components/auth/surface-state-card";
import { DashboardHero } from "@/components/admin/dashboard-primitives";
import { SetupCallout } from "@/components/setup-callout";
import {
  getAuthIssuePresentation,
  getOperationIssueMessage,
} from "@/lib/auth/presentation";
import { BUSINESS_ADMIN_ROUTE } from "@/lib/auth/constants";
import { appEnv } from "@/lib/env";
import { requireStaffActor } from "@/lib/server/auth";
import { AppError } from "@/lib/server/errors";
import {
  getExceptionsReport,
  listBranches,
  listPlans,
  listStaffUsers,
  getOverviewReport,
  getStaffActivityReport,
} from "@/lib/server/loyalty-service";

export default async function BusinessAdminPage() {
  if (!appEnv.hasFirebaseAuth || !appEnv.hasFirebaseAdmin) {
    return (
      <main className="container-edge py-10">
        <SetupCallout
          title="Business admin sign-in is not configured"
          message="Finish sign-in setup and invite an approved business admin to unlock this workspace."
          actionHref={`/sign-in?redirectTo=${encodeURIComponent(BUSINESS_ADMIN_ROUTE)}`}
          actionLabel="Open sign-in"
        />
      </main>
    );
  }

  let actor:
    | Awaited<ReturnType<typeof requireStaffActor>>
    | null = null;
  let accessIssue: ReturnType<typeof getAuthIssuePresentation> | null = null;
  let dataIssueMessage: string | null = null;
  let overview: Awaited<ReturnType<typeof getOverviewReport>> | null = null;
  let branches: Awaited<ReturnType<typeof listBranches>> | null = null;
  let plans: Awaited<ReturnType<typeof listPlans>> | null = null;
  let staffUsers: Awaited<ReturnType<typeof listStaffUsers>> | null = null;
  let staffActivity:
    | Awaited<ReturnType<typeof getStaffActivityReport>>
    | null = null;
  let exceptions:
    | Awaited<ReturnType<typeof getExceptionsReport>>
    | null = null;

  try {
    actor = await requireStaffActor("MERCHANT_ADMIN");
  } catch (error) {
    accessIssue = getAuthIssuePresentation(error, "merchant");
  }

  if (actor) {
    try {
      [overview, branches, plans, staffUsers, staffActivity, exceptions] = await Promise.all([
        getOverviewReport(actor),
        listBranches(actor),
        listPlans(actor),
        listStaffUsers(actor),
        getStaffActivityReport(actor),
        getExceptionsReport(actor),
      ]);
    } catch (error) {
      dataIssueMessage = getOperationIssueMessage(
        error,
        "The business admin reports could not be loaded right now. Refresh the page and try again.",
      );
    }
  }

  if (!actor) {
    const issue =
      accessIssue ??
      getAuthIssuePresentation(
        new AppError("Unauthorized.", 401, "UNAUTHORIZED"),
        "merchant",
      );

    return (
      <main className="container-edge flex min-h-screen items-center justify-center py-16">
        <SurfaceStateCard
          surface="merchant"
          title={issue.title}
          message={issue.message}
          primaryActionHref={`/sign-in?redirectTo=${encodeURIComponent(BUSINESS_ADMIN_ROUTE)}`}
          primaryActionLabel="Open Business Admin Sign-In"
          showSignOut={issue.showSignOut}
        />
      </main>
    );
  }

  if (!overview || !branches || !plans || !staffUsers || !staffActivity || !exceptions) {
    return (
      <main className="container-edge flex min-h-screen items-center justify-center py-16">
        <SurfaceStateCard
          surface="merchant"
          title="Business Admin Unavailable"
          message={
            dataIssueMessage ??
            "The business admin reports could not be loaded right now. Refresh the page and try again."
          }
          primaryActionHref={BUSINESS_ADMIN_ROUTE}
          primaryActionLabel="Retry Business Admin"
          showSignOut
        />
      </main>
    );
  }

  return (
    <main className="container-edge min-h-screen space-y-8 py-6 sm:py-10">
      <DashboardHero
        eyebrow="Business admin"
        title="Business operations dashboard"
        description="Manage branch rollout, reward plan coverage, staff access, and exception handling from one operating surface."
        actions={<SignOutButton />}
        stats={[
          {
            label: "Active members",
            value: overview.activeMemberships,
          },
          {
            label: "Branches",
            value: overview.branchCount,
          },
          {
            label: "Plans",
            value: plans.length,
          },
          {
            label: "Managers",
            value: overview.managerCount,
          },
        ]}
      />
      <MerchantAdminDashboard
        tenantId={actor.tenantId}
        overview={overview}
        branches={branches}
        plans={plans}
        staffUsers={staffUsers}
        staffActivity={staffActivity}
        exceptions={exceptions}
      />
    </main>
  );
}
