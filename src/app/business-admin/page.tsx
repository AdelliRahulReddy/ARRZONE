import { SignOutButton } from "@/components/auth/sign-out-button";
import { MerchantAdminDashboard } from "@/components/merchant/merchant-admin-dashboard";
import { SurfaceStateCard } from "@/components/auth/surface-state-card";
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
          title="Business admin requires Firebase Auth"
          message="Configure Firebase Auth and seed staff_users records to unlock the admin portal."
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
    <main className="container-edge min-h-screen space-y-6 py-10">
      <div className="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div className="space-y-2">
          <p className="text-sm uppercase tracking-[0.24em] text-muted-foreground">
            Business admin
          </p>
          <h1 className="text-4xl font-semibold tracking-tight">
            Business operations dashboard
          </h1>
          <p className="text-lg leading-7 text-muted-foreground">
            Manage branches, reward plans, and tenant staff from one Firestore-backed console.
          </p>
        </div>
        <SignOutButton />
      </div>
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
