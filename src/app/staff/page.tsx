import { SignOutButton } from "@/components/auth/sign-out-button";
import { SurfaceStateCard } from "@/components/auth/surface-state-card";
import { DashboardHero } from "@/components/admin/dashboard-primitives";
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
    <main className="container-edge min-h-screen space-y-8 py-6 sm:py-10">
      <DashboardHero
        eyebrow="Store operations"
        title={`${getStaffRoleDisplayName(actor.role)} workspace`}
        description="Open members, add purchases, redeem rewards, and manage exception flows across the branches assigned to this account."
        actions={<SignOutButton />}
        stats={[
          {
            label: "Assigned branches",
            value: workspace.accessibleBranches.length,
          },
          {
            label: "Active members",
            value: workspace.activeMembershipCount,
          },
          {
            label: "Recent members",
            value: workspace.recentMemberships.length,
          },
          {
            label: "Access role",
            value: getStaffRoleDisplayName(actor.role),
          },
        ]}
      />
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
