import { SignOutButton } from "@/components/auth/sign-out-button";
import { SurfaceStateCard } from "@/components/auth/surface-state-card";
import { SetupCallout } from "@/components/setup-callout";
import { StaffConsole } from "@/components/staff/staff-console";
import {
  getAuthIssuePresentation,
  getOperationIssueMessage,
} from "@/lib/auth/presentation";
import { getStaffRoleDisplayName } from "@/lib/auth/role-labels";
import { appEnv } from "@/lib/env";
import { getCurrentStaffActor } from "@/lib/server/auth";
import { AppError } from "@/lib/server/errors";
import { getStaffWorkspaceSnapshot } from "@/lib/server/loyalty-service";

export default async function StaffPage() {
  if (!appEnv.hasFirebaseAuth || !appEnv.hasFirebaseAdmin) {
    return (
      <main className="container-edge py-10">
        <SetupCallout
          title="Store operations sign-in is not configured"
          message="Finish sign-in setup to enable protected store operations and branch-based access."
          actionHref="/sign-in?redirectTo=%2Fstaff"
          actionLabel="Open sign-in route"
        />
      </main>
    );
  }

  let actor: Awaited<ReturnType<typeof getCurrentStaffActor>> | null = null;
  let accessIssue: ReturnType<typeof getAuthIssuePresentation> | null = null;
  let dataIssueMessage: string | null = null;
  let workspace: Awaited<ReturnType<typeof getStaffWorkspaceSnapshot>> | null = null;

  try {
    actor = await getCurrentStaffActor();
  } catch (error) {
    accessIssue = getAuthIssuePresentation(error, "staff");
  }

  if (actor) {
    try {
      workspace = await getStaffWorkspaceSnapshot(actor);
    } catch (error) {
      dataIssueMessage = getOperationIssueMessage(
        error,
        "Store operations could not be loaded right now. Refresh the page and try again.",
      );
    }
  }

  if (!actor) {
    const issue =
      accessIssue ??
      getAuthIssuePresentation(
        new AppError("Unauthorized.", 401, "UNAUTHORIZED"),
        "staff",
      );

    return (
      <main className="container-edge flex min-h-screen items-center justify-center py-16">
        <SurfaceStateCard
          surface="staff"
          title={issue.title}
          message={issue.message}
          primaryActionHref="/sign-in?redirectTo=%2Fstaff"
          primaryActionLabel="Open Store Operations Sign-In"
          showSignOut={issue.showSignOut}
        />
      </main>
    );
  }

  if (!workspace) {
    return (
      <main className="container-edge flex min-h-screen items-center justify-center py-16">
        <SurfaceStateCard
          surface="staff"
          title="Store Operations Unavailable"
          message={
            dataIssueMessage ??
            "Store operations could not be loaded right now. Refresh the page and try again."
          }
          primaryActionHref="/staff"
          primaryActionLabel="Retry Store Operations"
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
            Store operations
          </p>
          <h1 className="text-4xl font-semibold tracking-tight">
            {getStaffRoleDisplayName(actor.role)} workspace
          </h1>
          <p className="text-lg text-muted-foreground">
            Open members, add purchases, and redeem rewards for the branches assigned to this account.
          </p>
        </div>
        <SignOutButton />
      </div>
      <StaffConsole
        role={actor.role}
        accessibleBranches={workspace.accessibleBranches}
        activeMembershipCount={workspace.activeMembershipCount}
        recentMemberships={workspace.recentMemberships}
        branchPerformance={workspace.branchPerformance}
        teamMembers={workspace.teamMembers}
      />
    </main>
  );
}
