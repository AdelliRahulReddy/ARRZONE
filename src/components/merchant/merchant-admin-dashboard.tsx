"use client";

import Link from "next/link";
import { startTransition, useEffect, useMemo, useState, type FormEvent } from "react";
import { format } from "date-fns";
import {
  AlertTriangle,
  Building2,
  LayoutGrid,
  Plus,
  ShieldAlert,
  Sparkles,
  Users,
} from "lucide-react";
import { useRouter } from "next/navigation";
import {
  AdminMetricCard,
  DashboardEmptyState,
  DashboardStatusBadge,
} from "@/components/admin/dashboard-primitives";
import { Alert, AlertDescription, AlertTitle } from "@/components/ui/alert";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from "@/components/ui/dialog";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from "@/components/ui/table";
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs";
import { QrCodeBox } from "@/components/qr-code-box";
import { getStaffRoleDisplayName, ROLE_LABELS } from "@/lib/auth/role-labels";
import type {
  BranchDoc,
  PlanDoc,
  SecurityEventDoc,
  StaffUserDoc,
} from "@/lib/firebase/model";

type MerchantAdminDashboardProps = {
  tenantId: string;
  overview: {
    totalMemberships: number;
    activeMemberships: number;
    rewardsRedeemed: number;
    branchCount: number;
    managerCount: number;
  };
  branches: BranchDoc[];
  plans: PlanDoc[];
  staffUsers: StaffUserDoc[];
  staffActivity: Array<{
    id: string;
    fullName: string;
    email: string;
    role: StaffUserDoc["role"];
    status: StaffUserDoc["status"];
    primaryBranchId: string | null;
    primaryBranchName: string | null;
    branchIds: string[];
    branchNames: string[];
    purchaseAdds: number;
    rewardsRedeemed: number;
    reversals: number;
    totalActions: number;
    lastActionAt: string | null;
    canManageStatus: boolean;
    isCurrentUser: boolean;
  }>;
  exceptions: SecurityEventDoc[];
};

type RequestPayload<T> = {
  ok: boolean;
  data?: T;
  error?: {
    message?: string;
  };
};

function formatDate(value: string) {
  return format(new Date(value), "dd MMM yyyy");
}

async function requestJson<T>(input: RequestInfo, init: RequestInit) {
  const response = await fetch(input, {
    ...init,
    headers: {
      "Content-Type": "application/json",
      ...(init.headers ?? {}),
    },
  });

  const payload = (await response.json().catch(() => null)) as RequestPayload<T> | null;
  if (!response.ok || !payload?.ok) {
    throw new Error(payload?.error?.message || "Request failed.");
  }

  return payload.data as T;
}

export function MerchantAdminDashboard({
  tenantId,
  overview,
  branches,
  plans,
  staffUsers,
  staffActivity,
  exceptions,
}: MerchantAdminDashboardProps) {
  const router = useRouter();
  const [statusPendingId, setStatusPendingId] = useState<string | null>(null);
  const [statusError, setStatusError] = useState("");
  const activePlans = useMemo(
    () => plans.filter((plan) => plan.status === "ACTIVE").length,
    [plans],
  );
  const invitedStaff = useMemo(
    () => staffUsers.filter((staff) => staff.status === "INVITED").length,
    [staffUsers],
  );
  const managerRows = useMemo(
    () => staffActivity.filter((staff) => staff.role === "MANAGER"),
    [staffActivity],
  );
  const topManager = useMemo(
    () =>
      [...managerRows].sort((left, right) => right.totalActions - left.totalActions)[0] ??
      null,
    [managerRows],
  );
  const totalTeamActions = useMemo(
    () => staffActivity.reduce((total, staff) => total + staff.totalActions, 0),
    [staffActivity],
  );

  async function handleStaffStatusChange(
    staffUserId: string,
    nextStatus: "ACTIVE" | "DISABLED",
  ) {
    setStatusPendingId(staffUserId);
    setStatusError("");

    try {
      await requestJson(`/api/v1/staff-users/${staffUserId}`, {
        method: "PATCH",
        body: JSON.stringify({ status: nextStatus }),
      });
      startTransition(() => {
        router.refresh();
      });
    } catch (error) {
      setStatusError(
        error instanceof Error ? error.message : "Failed to update staff status.",
      );
    } finally {
      setStatusPendingId(null);
    }
  }

  return (
    <div className="space-y-8">
      <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
        <AdminMetricCard
          title="Active Members"
          value={overview.activeMemberships}
          description="Members who can currently earn or redeem in this business."
          icon={LayoutGrid}
        />
        <AdminMetricCard
          title="Memberships"
          value={overview.totalMemberships}
          description="Total live memberships in this business."
          icon={Sparkles}
        />
        <AdminMetricCard
          title="Branches"
          value={overview.branchCount}
          description="Store locations currently active for this business."
          icon={Building2}
        />
        <AdminMetricCard
          title="Store Managers"
          value={overview.managerCount}
          description="Managers with store-level oversight and exception controls."
          icon={Users}
        />
        <AdminMetricCard
          title="Pending Invites"
          value={invitedStaff}
          description="Staff records still waiting for first verified login."
          icon={Users}
        />
      </div>

      <Tabs defaultValue="overview" className="space-y-5">
        <TabsList
          variant="line"
          className="w-full justify-start overflow-x-auto rounded-full border border-border/60 bg-background/70 p-1 shadow-[inset_0_1px_0_rgba(255,255,255,0.72)]"
        >
          <TabsTrigger value="overview" className="min-w-fit rounded-full px-4">
            Overview
          </TabsTrigger>
          <TabsTrigger value="plans" className="min-w-fit rounded-full px-4">
            Plans
          </TabsTrigger>
          <TabsTrigger value="branches" className="min-w-fit rounded-full px-4">
            Branches
          </TabsTrigger>
          <TabsTrigger value="team" className="min-w-fit rounded-full px-4">
            Team
          </TabsTrigger>
          <TabsTrigger value="security" className="min-w-fit rounded-full px-4">
            Security
          </TabsTrigger>
        </TabsList>

        <TabsContent value="overview" className="space-y-4">
          <div className="grid gap-4 lg:grid-cols-[1.15fr_0.85fr]">
            <div className="rounded-[1.9rem] border border-border/60 bg-card/95 p-6 shadow-[0_24px_70px_-42px_rgba(15,23,42,0.18)]">
              <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                  <p className="text-sm uppercase tracking-[0.24em] text-muted-foreground">
                    Tenant Snapshot
                  </p>
                  <h2 className="mt-2 text-2xl font-semibold tracking-tight">
                    Merchant operations for {tenantId}
                  </h2>
                </div>
                <Badge variant="outline" className="rounded-full px-3 py-1">
                  {branches.length} branch{branches.length === 1 ? "" : "es"}
                </Badge>
              </div>
              <div className="mt-6 grid gap-4 md:grid-cols-2">
                <div className="rounded-[1.35rem] border border-border/60 bg-background/80 p-4 shadow-[inset_0_1px_0_rgba(255,255,255,0.72)]">
                  <p className="text-sm font-medium">Store manager coverage</p>
                  <p className="mt-2 text-sm leading-6 text-muted-foreground">
                    {managerRows.length === 0
                      ? "No store managers are assigned yet. Invite at least one manager to cover store-level operations."
                      : `${managerRows.length} store manager${managerRows.length === 1 ? "" : "s"} are assigned across ${overview.branchCount} branch${overview.branchCount === 1 ? "" : "es"}.`}
                  </p>
                  {topManager ? (
                    <p className="mt-3 text-sm font-medium text-foreground">
                      Top manager activity: {topManager.fullName} with {topManager.totalActions} logged actions.
                    </p>
                  ) : null}
                </div>
                <div className="rounded-[1.35rem] border border-border/60 bg-background/80 p-4 shadow-[inset_0_1px_0_rgba(255,255,255,0.72)]">
                  <p className="text-sm font-medium">Operational throughput</p>
                  <p className="mt-2 text-sm leading-6 text-muted-foreground">
                    Staff activity is measured from immutable ledger events so purchases,
                    redemptions, reversals, and recovery actions roll up into team reporting.
                  </p>
                  <div className="mt-3 flex flex-wrap gap-2 text-sm text-foreground">
                    <Badge variant="outline" className="rounded-full">
                      {overview.rewardsRedeemed} rewards redeemed
                    </Badge>
                    <Badge variant="outline" className="rounded-full">
                      {totalTeamActions} total staff actions
                    </Badge>
                    <Badge variant="outline" className="rounded-full">
                      {activePlans} active plans
                    </Badge>
                  </div>
                </div>
              </div>
            </div>

            <div className="rounded-3xl border border-border/70 bg-card/90 p-6 shadow-sm">
              <p className="text-sm uppercase tracking-[0.24em] text-muted-foreground">
                Quick Actions
              </p>
              <div className="mt-4 flex flex-col gap-3">
                <CreateBranchDialog tenantId={tenantId} />
                <CreatePlanDialog tenantId={tenantId} branches={branches} />
                <InviteStaffDialog tenantId={tenantId} branches={branches} />
              </div>
            </div>
          </div>
        </TabsContent>

        <TabsContent value="plans" className="space-y-4">
          <div className="rounded-3xl border border-border/70 bg-card/90 p-6 shadow-sm">
            <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
              <div>
                <p className="text-sm uppercase tracking-[0.24em] text-muted-foreground">
                  Plans
                </p>
                <h2 className="mt-2 text-2xl font-semibold tracking-tight">
                  Reward plan configuration
                </h2>
              </div>
              <CreatePlanDialog tenantId={tenantId} branches={branches} />
            </div>
            <div className="mt-6">
              {plans.length === 0 ? (
                <DashboardEmptyState
                  title="No plans created yet"
                  description="Create your first plan to define thresholds, reward credits, and branch applicability."
                />
              ) : (
                <Table>
                  <TableHeader>
                    <TableRow>
                      <TableHead>Name</TableHead>
                      <TableHead>Status</TableHead>
                      <TableHead>Threshold</TableHead>
                      <TableHead>Reward</TableHead>
                      <TableHead>Branch Scope</TableHead>
                      <TableHead>Updated</TableHead>
                      <TableHead className="text-right">Action</TableHead>
                    </TableRow>
                  </TableHeader>
                  <TableBody>
                    {plans.map((plan) => (
                      <TableRow key={plan.id}>
                        <TableCell>
                          <div>
                            <p className="font-medium">{plan.name}</p>
                            <p className="text-xs text-muted-foreground">{plan.id}</p>
                          </div>
                        </TableCell>
                        <TableCell>
                          <DashboardStatusBadge value={plan.status} />
                        </TableCell>
                        <TableCell>{plan.thresholdCount}</TableCell>
                        <TableCell>{plan.rewardCreditCount}</TableCell>
                        <TableCell>
                          {plan.applicableBranchIds.length === 0
                            ? "All branches"
                            : `${plan.applicableBranchIds.length} branch`}
                        </TableCell>
                        <TableCell>{formatDate(plan.updatedAt)}</TableCell>
                        <TableCell className="text-right">
                          <EditPlanDialog plan={plan} branches={branches} />
                        </TableCell>
                      </TableRow>
                    ))}
                  </TableBody>
                </Table>
              )}
            </div>
          </div>
        </TabsContent>

        <TabsContent value="branches" className="space-y-4">
          <div className="rounded-3xl border border-border/70 bg-card/90 p-6 shadow-sm">
            <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
              <div>
                <p className="text-sm uppercase tracking-[0.24em] text-muted-foreground">
                  Branches
                </p>
                <h2 className="mt-2 text-2xl font-semibold tracking-tight">
                  Branch directory and member join links
                </h2>
              </div>
              <CreateBranchDialog tenantId={tenantId} />
            </div>
            <div className="mt-6">
              {branches.length === 0 ? (
                <DashboardEmptyState
                  title="No branches created yet"
                  description="Create at least one branch so enrollment, staff access, and plan applicability have a real operational scope."
                />
              ) : (
                <>
                  <div className="grid gap-3 md:hidden">
                    {branches.map((branch) => (
                      <div
                        key={branch.id}
                        className="rounded-2xl border border-border/70 bg-background/80 p-4"
                      >
                        <div className="flex items-start justify-between gap-3">
                          <div className="space-y-1">
                            <p className="font-medium">{branch.name}</p>
                            <p className="text-xs text-muted-foreground">{branch.id}</p>
                          </div>
                          <DashboardStatusBadge value={branch.status} />
                        </div>
                        <div className="mt-4 flex flex-wrap gap-2 text-sm text-muted-foreground">
                          <Badge variant="outline" className="rounded-full">
                            {branch.code}
                          </Badge>
                          <Badge variant="outline" className="rounded-full">
                            {branch.timezone}
                          </Badge>
                          <Badge variant="outline" className="rounded-full">
                            Created {formatDate(branch.createdAt)}
                          </Badge>
                        </div>
                        <div className="mt-4 flex flex-wrap gap-2">
                          <BranchJoinToolsDialog branch={branch} />
                          <EditBranchDialog branch={branch} />
                        </div>
                      </div>
                    ))}
                  </div>
                  <div className="hidden md:block">
                    <Table>
                      <TableHeader>
                        <TableRow>
                          <TableHead>Name</TableHead>
                          <TableHead>Code</TableHead>
                          <TableHead>Member Join</TableHead>
                          <TableHead>Status</TableHead>
                          <TableHead>Timezone</TableHead>
                          <TableHead>Created</TableHead>
                          <TableHead className="text-right">Action</TableHead>
                        </TableRow>
                      </TableHeader>
                      <TableBody>
                        {branches.map((branch) => (
                          <TableRow key={branch.id}>
                            <TableCell>
                              <div>
                                <p className="font-medium">{branch.name}</p>
                                <p className="text-xs text-muted-foreground">{branch.id}</p>
                              </div>
                            </TableCell>
                            <TableCell>
                              <Badge variant="outline" className="rounded-full">
                                {branch.code}
                              </Badge>
                            </TableCell>
                            <TableCell>
                              <BranchJoinToolsDialog branch={branch} />
                            </TableCell>
                            <TableCell>
                              <DashboardStatusBadge value={branch.status} />
                            </TableCell>
                            <TableCell>{branch.timezone}</TableCell>
                            <TableCell>{formatDate(branch.createdAt)}</TableCell>
                            <TableCell className="text-right">
                              <EditBranchDialog branch={branch} />
                            </TableCell>
                          </TableRow>
                        ))}
                      </TableBody>
                    </Table>
                  </div>
                </>
              )}
            </div>
          </div>
        </TabsContent>

        <TabsContent value="team" className="space-y-4">
          <div className="rounded-3xl border border-border/70 bg-card/90 p-6 shadow-sm">
            <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
              <div>
                <p className="text-sm uppercase tracking-[0.24em] text-muted-foreground">
                  Team
                </p>
                <h2 className="mt-2 text-2xl font-semibold tracking-tight">
                  Store managers, counter staff, and business admins
                </h2>
              </div>
              <InviteStaffDialog tenantId={tenantId} branches={branches} />
            </div>
            <div className="mt-6 space-y-4">
              {statusError ? (
                <Alert variant="destructive">
                  <AlertTriangle />
                  <AlertTitle>Team update failed</AlertTitle>
                  <AlertDescription>{statusError}</AlertDescription>
                </Alert>
              ) : null}
              {staffActivity.length === 0 ? (
                <DashboardEmptyState
                  title="No staff invited yet"
                  description="Invite counter staff, store managers, or another business admin to start operating the tenant."
                />
              ) : (
                <>
                  <div className="grid gap-3 md:hidden">
                    {staffActivity.map((staff) => {
                      const primaryBranch = staff.primaryBranchName ?? "Unassigned";
                      const shouldActivate = staff.status !== "ACTIVE";
                      const nextStatus = shouldActivate ? "ACTIVE" : "DISABLED";
                      const actionLabel = shouldActivate ? "Activate" : "Disable";

                      return (
                        <div
                          key={staff.id}
                          className="rounded-2xl border border-border/70 bg-background/80 p-4"
                        >
                          <div className="flex items-start justify-between gap-3">
                            <div className="space-y-1">
                              <p className="font-medium">{staff.fullName}</p>
                              <p className="text-sm text-muted-foreground">{staff.email}</p>
                              <p className="text-xs text-muted-foreground">{staff.id}</p>
                            </div>
                            <DashboardStatusBadge value={staff.status} />
                          </div>
                          <div className="mt-4 grid grid-cols-2 gap-3 text-sm">
                            <div>
                              <p className="text-xs uppercase tracking-[0.2em] text-muted-foreground">
                                Role
                              </p>
                              <p className="mt-1">{getStaffRoleDisplayName(staff.role)}</p>
                            </div>
                            <div>
                              <p className="text-xs uppercase tracking-[0.2em] text-muted-foreground">
                                Primary branch
                              </p>
                              <p className="mt-1">{primaryBranch}</p>
                            </div>
                            <div>
                              <p className="text-xs uppercase tracking-[0.2em] text-muted-foreground">
                                Actions
                              </p>
                              <p className="mt-1">{staff.totalActions}</p>
                            </div>
                            <div>
                              <p className="text-xs uppercase tracking-[0.2em] text-muted-foreground">
                                Last activity
                              </p>
                              <p className="mt-1">
                                {staff.lastActionAt ? formatDate(staff.lastActionAt) : "No activity"}
                              </p>
                            </div>
                          </div>
                          <p className="mt-4 text-sm text-muted-foreground">
                            {staff.branchNames.length === 0
                              ? "No branch assignments"
                              : staff.branchNames.join(", ")}
                          </p>
                          <div className="mt-4 flex flex-wrap gap-2">
                            <EditStaffDialog staff={staff} branches={branches} />
                            {staff.canManageStatus ? (
                              <Button
                                type="button"
                                variant={nextStatus === "DISABLED" ? "outline" : "default"}
                                className="rounded-full"
                                disabled={statusPendingId === staff.id}
                                onClick={() =>
                                  void handleStaffStatusChange(staff.id, nextStatus)
                                }
                              >
                                {statusPendingId === staff.id ? "Saving..." : actionLabel}
                              </Button>
                            ) : (
                              <Badge variant="secondary" className="rounded-full">
                                {staff.isCurrentUser ? "Current account" : "Read only"}
                              </Badge>
                            )}
                          </div>
                        </div>
                      );
                    })}
                  </div>
                  <div className="hidden md:block">
                    <Table>
                      <TableHeader>
                        <TableRow>
                          <TableHead>Name</TableHead>
                          <TableHead>Email</TableHead>
                          <TableHead>Role</TableHead>
                          <TableHead>Status</TableHead>
                          <TableHead>Primary Branch</TableHead>
                          <TableHead>Branch Scope</TableHead>
                          <TableHead>Purchases</TableHead>
                          <TableHead>Rewards</TableHead>
                          <TableHead>Total Actions</TableHead>
                          <TableHead>Last Activity</TableHead>
                          <TableHead className="text-right">Action</TableHead>
                        </TableRow>
                      </TableHeader>
                      <TableBody>
                        {staffActivity.map((staff) => {
                          const primaryBranch = staff.primaryBranchName ?? "Unassigned";
                          const shouldActivate = staff.status !== "ACTIVE";
                          const nextStatus = shouldActivate ? "ACTIVE" : "DISABLED";
                          const actionLabel = shouldActivate ? "Activate" : "Disable";

                          return (
                            <TableRow key={staff.id}>
                              <TableCell>
                                <div>
                                  <p className="font-medium">{staff.fullName}</p>
                                  <p className="text-xs text-muted-foreground">{staff.id}</p>
                                </div>
                              </TableCell>
                              <TableCell>{staff.email}</TableCell>
                              <TableCell>{getStaffRoleDisplayName(staff.role)}</TableCell>
                              <TableCell>
                                <DashboardStatusBadge value={staff.status} />
                              </TableCell>
                              <TableCell>{primaryBranch}</TableCell>
                              <TableCell>
                                {staff.branchNames.length === 0
                                  ? "No branch assignments"
                                  : staff.branchNames.join(", ")}
                              </TableCell>
                              <TableCell>{staff.purchaseAdds}</TableCell>
                              <TableCell>{staff.rewardsRedeemed}</TableCell>
                              <TableCell>{staff.totalActions}</TableCell>
                              <TableCell>
                                {staff.lastActionAt ? formatDate(staff.lastActionAt) : "No activity"}
                              </TableCell>
                              <TableCell className="text-right">
                                <div className="flex justify-end gap-2">
                                  <EditStaffDialog staff={staff} branches={branches} />
                                  {staff.canManageStatus ? (
                                    <Button
                                      type="button"
                                      variant={nextStatus === "DISABLED" ? "outline" : "default"}
                                      className="rounded-full"
                                      disabled={statusPendingId === staff.id}
                                      onClick={() =>
                                        void handleStaffStatusChange(staff.id, nextStatus)
                                      }
                                    >
                                      {statusPendingId === staff.id ? "Saving..." : actionLabel}
                                    </Button>
                                  ) : (
                                    <Badge variant="secondary" className="rounded-full">
                                      {staff.isCurrentUser ? "Current account" : "Read only"}
                                    </Badge>
                                  )}
                                </div>
                              </TableCell>
                            </TableRow>
                          );
                        })}
                      </TableBody>
                    </Table>
                  </div>
                </>
              )}
            </div>
          </div>
        </TabsContent>

        <TabsContent value="security" className="space-y-4">
          <div className="rounded-3xl border border-border/70 bg-card/90 p-6 shadow-sm">
            <div className="flex items-start gap-3 rounded-2xl border border-border/70 bg-background/70 p-4">
              <ShieldAlert className="mt-0.5 size-5 text-primary" />
              <div>
                <p className="font-medium">Tenant security signal feed</p>
                <p className="mt-2 text-sm leading-6 text-muted-foreground">
                  These are the most recent security events recorded for this
                  business, including suspicious activity, rate limits, and corrective actions.
                </p>
              </div>
            </div>
            <div className="mt-6">
              {exceptions.length === 0 ? (
                <DashboardEmptyState
                  title="No security events recorded"
                  description="Events appear here when rate limits, suspicious activity, or corrective flows are recorded for this business."
                />
              ) : (
                <Table>
                  <TableHeader>
                    <TableRow>
                      <TableHead>Event</TableHead>
                      <TableHead>Scope</TableHead>
                      <TableHead>Branch</TableHead>
                      <TableHead>Created</TableHead>
                    </TableRow>
                  </TableHeader>
                  <TableBody>
                    {exceptions.map((event) => (
                      <TableRow key={event.id}>
                        <TableCell>{event.eventType}</TableCell>
                        <TableCell>{event.scopeKey}</TableCell>
                        <TableCell>{event.branchId ?? "Platform-wide"}</TableCell>
                        <TableCell>{formatDate(event.createdAt)}</TableCell>
                      </TableRow>
                    ))}
                  </TableBody>
                </Table>
              )}
            </div>
          </div>
        </TabsContent>
      </Tabs>
    </div>
  );
}

function CreateBranchDialog({ tenantId }: { tenantId: string }) {
  const router = useRouter();
  const [open, setOpen] = useState(false);
  const [pending, setPending] = useState(false);
  const [errorMessage, setErrorMessage] = useState("");

  async function handleSubmit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();
    setPending(true);
    setErrorMessage("");

    const form = event.currentTarget;
    const formData = new FormData(form);

    try {
      await requestJson("/api/v1/branches", {
        method: "POST",
        body: JSON.stringify({
          tenantId,
          code: String(formData.get("code") ?? ""),
          name: String(formData.get("name") ?? ""),
          timezone: String(formData.get("timezone") ?? ""),
          address: String(formData.get("address") ?? ""),
        }),
      });
      form.reset();
      setOpen(false);
      startTransition(() => {
        router.refresh();
      });
    } catch (error) {
      setErrorMessage(error instanceof Error ? error.message : "Failed to create branch.");
    } finally {
      setPending(false);
    }
  }

  return (
    <Dialog open={open} onOpenChange={setOpen}>
      <DialogTrigger asChild>
        <Button className="rounded-full">
          <Plus />
          Create Branch
        </Button>
      </DialogTrigger>
      <DialogContent className="sm:max-w-lg">
        <DialogHeader>
          <DialogTitle>Create Branch</DialogTitle>
          <DialogDescription>
            Add a branch code and location identity for enrollment and staff scoping.
          </DialogDescription>
        </DialogHeader>
        <form className="space-y-4" onSubmit={handleSubmit}>
          <div className="grid gap-4 sm:grid-cols-2">
            <div className="space-y-2">
              <Label htmlFor="branch-name">Branch name</Label>
              <Input id="branch-name" name="name" placeholder="Jubilee Hills" required />
            </div>
            <div className="space-y-2">
              <Label htmlFor="branch-code">Join code</Label>
              <Input id="branch-code" name="code" placeholder="jubilee-hills" required />
            </div>
          </div>
          <div className="grid gap-4 sm:grid-cols-2">
            <div className="space-y-2">
              <Label htmlFor="branch-timezone">Timezone</Label>
              <Input
                id="branch-timezone"
                name="timezone"
                defaultValue="Asia/Calcutta"
                required
              />
            </div>
            <div className="space-y-2">
              <Label htmlFor="branch-address">Address</Label>
              <Input id="branch-address" name="address" placeholder="Optional" />
            </div>
          </div>
          {errorMessage ? (
            <Alert variant="destructive">
              <AlertTriangle />
              <AlertTitle>Branch creation failed</AlertTitle>
              <AlertDescription>{errorMessage}</AlertDescription>
            </Alert>
          ) : null}
          <DialogFooter>
            <Button type="submit" className="rounded-full" disabled={pending}>
              {pending ? "Creating..." : "Create Branch"}
            </Button>
          </DialogFooter>
        </form>
      </DialogContent>
    </Dialog>
  );
}

function BranchJoinToolsDialog({ branch }: { branch: BranchDoc }) {
  const [origin, setOrigin] = useState("");
  const [copyState, setCopyState] = useState<"idle" | "copied" | "failed">("idle");

  useEffect(() => {
    setOrigin(window.location.origin);
  }, []);

  const joinPath = `/join/${encodeURIComponent(branch.code)}`;
  const joinUrl = origin ? new URL(joinPath, origin).toString() : "";

  async function handleCopyLink() {
    if (!joinUrl) {
      return;
    }

    try {
      await navigator.clipboard.writeText(joinUrl);
      setCopyState("copied");
    } catch {
      setCopyState("failed");
    }
  }

  return (
    <Dialog>
      <DialogTrigger asChild>
        <Button variant="outline" className="rounded-full">
          Join link
        </Button>
      </DialogTrigger>
      <DialogContent className="sm:max-w-xl">
        <DialogHeader>
          <DialogTitle>{branch.name} join link</DialogTitle>
          <DialogDescription>
            Share this unique branch link with members so they can open the correct enrollment page directly.
          </DialogDescription>
        </DialogHeader>
        <div className="space-y-4">
          <div className="rounded-2xl border border-border/70 bg-background/80 p-4">
            <p className="text-xs uppercase tracking-[0.2em] text-muted-foreground">
              Branch join URL
            </p>
            <p className="mt-2 break-all text-sm leading-6 text-foreground">
              {joinUrl || joinPath}
            </p>
          </div>

          {joinUrl ? (
            <QrCodeBox
              value={joinUrl}
              label="Print or share this QR. Scanning it opens this branch's member enrollment page."
            />
          ) : (
            <div className="rounded-2xl border border-dashed border-border/80 bg-background/60 p-4 text-sm leading-6 text-muted-foreground">
              Preparing the branch QR...
            </div>
          )}

          <div className="flex flex-col gap-3 sm:flex-row">
            <Button
              type="button"
              className="rounded-full"
              onClick={() => void handleCopyLink()}
              disabled={!joinUrl}
            >
              {copyState === "copied"
                ? "Copied join link"
                : copyState === "failed"
                  ? "Copy failed"
                  : "Copy join link"}
            </Button>
            <Button asChild type="button" variant="outline" className="rounded-full">
              <Link href={joinPath} target="_blank">
                Open enrollment
              </Link>
            </Button>
          </div>

          <p className="text-sm leading-6 text-muted-foreground">
            Members can open this link directly. They do not need to type the branch code if you share the full link or QR.
          </p>
        </div>
      </DialogContent>
    </Dialog>
  );
}

function EditBranchDialog({ branch }: { branch: BranchDoc }) {
  const router = useRouter();
  const [open, setOpen] = useState(false);
  const [pending, setPending] = useState(false);
  const [status, setStatus] = useState<BranchDoc["status"]>(branch.status);
  const [errorMessage, setErrorMessage] = useState("");

  async function handleSubmit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();
    setPending(true);
    setErrorMessage("");

    const form = event.currentTarget;
    const formData = new FormData(form);

    try {
      await requestJson(`/api/v1/branches/${branch.id}`, {
        method: "PATCH",
        body: JSON.stringify({
          name: String(formData.get("name") ?? ""),
          timezone: String(formData.get("timezone") ?? ""),
          address: String(formData.get("address") ?? "") || null,
          status,
        }),
      });
      setOpen(false);
      startTransition(() => {
        router.refresh();
      });
    } catch (error) {
      setErrorMessage(error instanceof Error ? error.message : "Failed to update branch.");
    } finally {
      setPending(false);
    }
  }

  return (
    <Dialog open={open} onOpenChange={setOpen}>
      <DialogTrigger asChild>
        <Button variant="outline" className="rounded-full">
          Edit
        </Button>
      </DialogTrigger>
      <DialogContent className="sm:max-w-lg">
        <DialogHeader>
          <DialogTitle>Edit Branch</DialogTitle>
          <DialogDescription>
            Update branch presentation, join availability, and storefront details.
          </DialogDescription>
        </DialogHeader>
        <form className="space-y-4" onSubmit={handleSubmit}>
          <div className="grid gap-4 sm:grid-cols-2">
            <div className="space-y-2">
              <Label htmlFor={`branch-name-${branch.id}`}>Branch name</Label>
              <Input
                id={`branch-name-${branch.id}`}
                name="name"
                defaultValue={branch.name}
                required
              />
            </div>
            <div className="space-y-2">
              <Label htmlFor={`branch-timezone-${branch.id}`}>Timezone</Label>
              <Input
                id={`branch-timezone-${branch.id}`}
                name="timezone"
                defaultValue={branch.timezone}
                required
              />
            </div>
          </div>
          <div className="grid gap-4 sm:grid-cols-2">
            <div className="space-y-2">
              <Label htmlFor={`branch-address-${branch.id}`}>Address</Label>
              <Input
                id={`branch-address-${branch.id}`}
                name="address"
                defaultValue={branch.address ?? ""}
                placeholder="Optional"
              />
            </div>
            <div className="space-y-2">
              <Label>Status</Label>
              <Select value={status} onValueChange={(value) => setStatus(value as BranchDoc["status"])}>
                <SelectTrigger className="w-full">
                  <SelectValue placeholder="Select status" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="ACTIVE">Active</SelectItem>
                  <SelectItem value="INACTIVE">Inactive</SelectItem>
                </SelectContent>
              </Select>
            </div>
          </div>
          {errorMessage ? (
            <Alert variant="destructive">
              <AlertTriangle />
              <AlertTitle>Branch update failed</AlertTitle>
              <AlertDescription>{errorMessage}</AlertDescription>
            </Alert>
          ) : null}
          <DialogFooter>
            <Button type="submit" className="rounded-full" disabled={pending}>
              {pending ? "Saving..." : "Save Branch"}
            </Button>
          </DialogFooter>
        </form>
      </DialogContent>
    </Dialog>
  );
}

function CreatePlanDialog({
  tenantId,
  branches,
}: {
  tenantId: string;
  branches: BranchDoc[];
}) {
  const router = useRouter();
  const [open, setOpen] = useState(false);
  const [pending, setPending] = useState(false);
  const [scope, setScope] = useState("all");
  const [errorMessage, setErrorMessage] = useState("");

  async function handleSubmit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();
    setPending(true);
    setErrorMessage("");

    const form = event.currentTarget;
    const formData = new FormData(form);

    try {
      await requestJson("/api/v1/plans", {
        method: "POST",
        body: JSON.stringify({
          tenantId,
          name: String(formData.get("name") ?? ""),
          eligibleLabel: String(formData.get("eligibleLabel") ?? ""),
          thresholdCount: Number(formData.get("thresholdCount")),
          rewardCreditCount: Number(formData.get("rewardCreditCount")),
          applicableBranchIds: scope === "all" ? undefined : [scope],
        }),
      });
      form.reset();
      setScope("all");
      setOpen(false);
      startTransition(() => {
        router.refresh();
      });
    } catch (error) {
      setErrorMessage(error instanceof Error ? error.message : "Failed to create plan.");
    } finally {
      setPending(false);
    }
  }

  return (
    <Dialog open={open} onOpenChange={setOpen}>
      <DialogTrigger asChild>
        <Button className="rounded-full">
          <Plus />
          Create Plan
        </Button>
      </DialogTrigger>
      <DialogContent className="sm:max-w-lg">
        <DialogHeader>
          <DialogTitle>Create Plan</DialogTitle>
          <DialogDescription>
            Define the threshold, reward credits, and branch scope for a loyalty plan.
          </DialogDescription>
        </DialogHeader>
        <form className="space-y-4" onSubmit={handleSubmit}>
          <div className="grid gap-4 sm:grid-cols-2">
            <div className="space-y-2">
              <Label htmlFor="plan-name">Plan name</Label>
              <Input id="plan-name" name="name" placeholder="House Rewards" required />
            </div>
            <div className="space-y-2">
              <Label htmlFor="plan-label">Eligible label</Label>
              <Input id="plan-label" name="eligibleLabel" placeholder="Any drink" required />
            </div>
          </div>
          <div className="grid gap-4 sm:grid-cols-2">
            <div className="space-y-2">
              <Label htmlFor="plan-threshold">Threshold count</Label>
              <Input
                id="plan-threshold"
                name="thresholdCount"
                type="number"
                min="1"
                defaultValue="5"
                required
              />
            </div>
            <div className="space-y-2">
              <Label htmlFor="plan-reward">Reward credits</Label>
              <Input
                id="plan-reward"
                name="rewardCreditCount"
                type="number"
                min="1"
                defaultValue="1"
                required
              />
            </div>
          </div>
          <div className="space-y-2">
            <Label>Branch scope</Label>
            <Select value={scope} onValueChange={setScope}>
              <SelectTrigger className="w-full">
                <SelectValue placeholder="All branches" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="all">All current branches</SelectItem>
                {branches.map((branch) => (
                  <SelectItem key={branch.id} value={branch.id}>
                    {branch.name}
                  </SelectItem>
                ))}
              </SelectContent>
            </Select>
          </div>
          {errorMessage ? (
            <Alert variant="destructive">
              <AlertTriangle />
              <AlertTitle>Plan creation failed</AlertTitle>
              <AlertDescription>{errorMessage}</AlertDescription>
            </Alert>
          ) : null}
          <DialogFooter>
            <Button type="submit" className="rounded-full" disabled={pending}>
              {pending ? "Creating..." : "Create Plan"}
            </Button>
          </DialogFooter>
        </form>
      </DialogContent>
    </Dialog>
  );
}

function EditPlanDialog({
  plan,
  branches,
}: {
  plan: PlanDoc;
  branches: BranchDoc[];
}) {
  const router = useRouter();
  const [open, setOpen] = useState(false);
  const [pending, setPending] = useState(false);
  const [errorMessage, setErrorMessage] = useState("");
  const [status, setStatus] = useState<PlanDoc["status"]>(plan.status);
  const [scope, setScope] = useState(
    plan.applicableBranchIds.length === 1 ? plan.applicableBranchIds[0] : "all",
  );

  async function handleSubmit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();
    setPending(true);
    setErrorMessage("");

    const form = event.currentTarget;
    const formData = new FormData(form);

    try {
      await requestJson(`/api/v1/plans/${plan.id}`, {
        method: "PATCH",
        body: JSON.stringify({
          name: String(formData.get("name") ?? ""),
          eligibleLabel: String(formData.get("eligibleLabel") ?? ""),
          thresholdCount: Number(formData.get("thresholdCount")),
          rewardCreditCount: Number(formData.get("rewardCreditCount")),
          status,
          applicableBranchIds: scope === "all" ? [] : [scope],
        }),
      });
      setOpen(false);
      startTransition(() => {
        router.refresh();
      });
    } catch (error) {
      setErrorMessage(error instanceof Error ? error.message : "Failed to update plan.");
    } finally {
      setPending(false);
    }
  }

  return (
    <Dialog open={open} onOpenChange={setOpen}>
      <DialogTrigger asChild>
        <Button variant="outline" className="rounded-full">
          Edit
        </Button>
      </DialogTrigger>
      <DialogContent className="sm:max-w-lg">
        <DialogHeader>
          <DialogTitle>Edit Plan</DialogTitle>
          <DialogDescription>
            Update the mutable plan head. The service layer writes a new immutable plan version.
          </DialogDescription>
        </DialogHeader>
        <form className="space-y-4" onSubmit={handleSubmit}>
          <div className="grid gap-4 sm:grid-cols-2">
            <div className="space-y-2">
              <Label htmlFor={`plan-name-${plan.id}`}>Plan name</Label>
              <Input id={`plan-name-${plan.id}`} name="name" defaultValue={plan.name} required />
            </div>
            <div className="space-y-2">
              <Label htmlFor={`plan-label-${plan.id}`}>Eligible label</Label>
              <Input
                id={`plan-label-${plan.id}`}
                name="eligibleLabel"
                defaultValue={plan.eligibleLabel}
                required
              />
            </div>
          </div>
          <div className="grid gap-4 sm:grid-cols-2">
            <div className="space-y-2">
              <Label htmlFor={`plan-threshold-${plan.id}`}>Threshold count</Label>
              <Input
                id={`plan-threshold-${plan.id}`}
                name="thresholdCount"
                type="number"
                min="1"
                defaultValue={plan.thresholdCount}
                required
              />
            </div>
            <div className="space-y-2">
              <Label htmlFor={`plan-reward-${plan.id}`}>Reward credits</Label>
              <Input
                id={`plan-reward-${plan.id}`}
                name="rewardCreditCount"
                type="number"
                min="1"
                defaultValue={plan.rewardCreditCount}
                required
              />
            </div>
          </div>
          <div className="grid gap-4 sm:grid-cols-2">
            <div className="space-y-2">
              <Label>Status</Label>
              <Select value={status} onValueChange={(value) => setStatus(value as PlanDoc["status"])}>
                <SelectTrigger className="w-full">
                  <SelectValue placeholder="Select status" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="ACTIVE">Active</SelectItem>
                  <SelectItem value="INACTIVE">Inactive</SelectItem>
                </SelectContent>
              </Select>
            </div>
            <div className="space-y-2">
              <Label>Branch scope</Label>
              <Select value={scope} onValueChange={setScope}>
                <SelectTrigger className="w-full">
                  <SelectValue placeholder="All branches" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="all">All branches</SelectItem>
                  {branches.map((branch) => (
                    <SelectItem key={branch.id} value={branch.id}>
                      {branch.name}
                    </SelectItem>
                  ))}
                </SelectContent>
              </Select>
            </div>
          </div>
          {errorMessage ? (
            <Alert variant="destructive">
              <AlertTriangle />
              <AlertTitle>Plan update failed</AlertTitle>
              <AlertDescription>{errorMessage}</AlertDescription>
            </Alert>
          ) : null}
          <DialogFooter>
            <Button type="submit" className="rounded-full" disabled={pending}>
              {pending ? "Saving..." : "Save Changes"}
            </Button>
          </DialogFooter>
        </form>
      </DialogContent>
    </Dialog>
  );
}

function InviteStaffDialog({
  tenantId,
  branches,
}: {
  tenantId: string;
  branches: BranchDoc[];
}) {
  const router = useRouter();
  const [open, setOpen] = useState(false);
  const [pending, setPending] = useState(false);
  const [errorMessage, setErrorMessage] = useState("");
  const [role, setRole] = useState("CASHIER");
  const [primaryBranchId, setPrimaryBranchId] = useState(branches[0]?.id ?? "");
  const requiresBranchAssignment = role !== "MERCHANT_ADMIN";

  async function handleSubmit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();
    setPending(true);
    setErrorMessage("");

    const form = event.currentTarget;
    const formData = new FormData(form);
    const branchIds =
      role === "MERCHANT_ADMIN"
        ? branches.map((branch) => branch.id)
        : primaryBranchId
          ? [primaryBranchId]
          : [];

    try {
      await requestJson("/api/v1/staff-users", {
        method: "POST",
        body: JSON.stringify({
          tenantId,
          fullName: String(formData.get("fullName") ?? ""),
          email: String(formData.get("email") ?? ""),
          role,
          primaryBranchId: role === "MERCHANT_ADMIN" ? branchIds[0] ?? null : primaryBranchId,
          branchIds,
        }),
      });
      form.reset();
      setRole("CASHIER");
      setPrimaryBranchId(branches[0]?.id ?? "");
      setOpen(false);
      startTransition(() => {
        router.refresh();
      });
    } catch (error) {
      setErrorMessage(error instanceof Error ? error.message : "Failed to invite staff.");
    } finally {
      setPending(false);
    }
  }

  return (
    <Dialog open={open} onOpenChange={setOpen}>
      <DialogTrigger asChild>
        <Button className="rounded-full">
          <Plus />
          Invite Staff
        </Button>
      </DialogTrigger>
      <DialogContent className="sm:max-w-lg">
        <DialogHeader>
          <DialogTitle>Invite Staff</DialogTitle>
          <DialogDescription>
            Create a staff access record. The account links automatically on first verified login.
          </DialogDescription>
        </DialogHeader>
        <form className="space-y-4" onSubmit={handleSubmit}>
          <div className="grid gap-4 sm:grid-cols-2">
            <div className="space-y-2">
              <Label htmlFor="staff-full-name">Full name</Label>
              <Input id="staff-full-name" name="fullName" placeholder="Taylor Staff" required />
            </div>
            <div className="space-y-2">
              <Label htmlFor="staff-email-invite">Email</Label>
              <Input id="staff-email-invite" name="email" type="email" required />
            </div>
          </div>
          <div className="grid gap-4 sm:grid-cols-2">
            <div className="space-y-2">
              <Label>Role</Label>
              <Select
                value={role}
                onValueChange={(value) => {
                  setRole(value);
                  if (value === "MERCHANT_ADMIN") {
                    setPrimaryBranchId(branches[0]?.id ?? "");
                  }
                }}
              >
                <SelectTrigger className="w-full">
                  <SelectValue placeholder="Select role" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="CASHIER">{ROLE_LABELS.CASHIER}</SelectItem>
                  <SelectItem value="MANAGER">{ROLE_LABELS.MANAGER}</SelectItem>
                  <SelectItem value="MERCHANT_ADMIN">{ROLE_LABELS.MERCHANT_ADMIN}</SelectItem>
                </SelectContent>
              </Select>
            </div>
            <div className="space-y-2">
              <Label>Primary branch</Label>
              {requiresBranchAssignment ? (
                <Select value={primaryBranchId} onValueChange={setPrimaryBranchId}>
                  <SelectTrigger className="w-full">
                    <SelectValue placeholder="Select branch" />
                  </SelectTrigger>
                  <SelectContent>
                    {branches.map((branch) => (
                      <SelectItem key={branch.id} value={branch.id}>
                        {branch.name}
                      </SelectItem>
                    ))}
                  </SelectContent>
                </Select>
              ) : (
                <div className="rounded-lg border border-border/70 bg-background px-3 py-2 text-sm text-muted-foreground">
                  Business admins inherit all current branch assignments in this tenant.
                </div>
              )}
            </div>
          </div>
          {branches.length === 0 && requiresBranchAssignment ? (
            <Alert variant="destructive">
              <AlertTriangle />
              <AlertTitle>Create a branch first</AlertTitle>
              <AlertDescription>
                Staff records require a primary branch assignment.
              </AlertDescription>
            </Alert>
          ) : null}
          {errorMessage ? (
            <Alert variant="destructive">
              <AlertTriangle />
              <AlertTitle>Staff invite failed</AlertTitle>
              <AlertDescription>{errorMessage}</AlertDescription>
            </Alert>
          ) : null}
          <DialogFooter>
            <Button
              type="submit"
              className="rounded-full"
              disabled={pending || (requiresBranchAssignment && branches.length === 0)}
            >
              {pending ? "Inviting..." : "Create Staff Record"}
            </Button>
          </DialogFooter>
        </form>
      </DialogContent>
    </Dialog>
  );
}

function EditStaffDialog({
  staff,
  branches,
}: {
  staff: MerchantAdminDashboardProps["staffActivity"][number];
  branches: BranchDoc[];
}) {
  const router = useRouter();
  const [open, setOpen] = useState(false);
  const [pending, setPending] = useState(false);
  const [errorMessage, setErrorMessage] = useState("");
  const [role, setRole] = useState<StaffUserDoc["role"]>(staff.role);
  const [status, setStatus] = useState<StaffUserDoc["status"]>(staff.status);
  const [primaryBranchId, setPrimaryBranchId] = useState(staff.primaryBranchId ?? branches[0]?.id ?? "");
  const requiresBranchAssignment = role !== "MERCHANT_ADMIN";

  async function handleSubmit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();
    setPending(true);
    setErrorMessage("");

    const form = event.currentTarget;
    const formData = new FormData(form);
    const branchIds =
      role === "MERCHANT_ADMIN"
        ? branches.map((branch) => branch.id)
        : primaryBranchId
          ? [primaryBranchId]
          : [];

    try {
      await requestJson(`/api/v1/staff-users/${staff.id}`, {
        method: "PATCH",
        body: JSON.stringify({
          fullName: String(formData.get("fullName") ?? ""),
          email: String(formData.get("email") ?? ""),
          role,
          status,
          primaryBranchId: role === "MERCHANT_ADMIN" ? branchIds[0] ?? null : primaryBranchId,
          branchIds,
        }),
      });
      setOpen(false);
      startTransition(() => {
        router.refresh();
      });
    } catch (error) {
      setErrorMessage(error instanceof Error ? error.message : "Failed to update staff.");
    } finally {
      setPending(false);
    }
  }

  return (
    <Dialog open={open} onOpenChange={setOpen}>
      <DialogTrigger asChild>
        <Button variant="outline" className="rounded-full">
          Edit
        </Button>
      </DialogTrigger>
      <DialogContent className="sm:max-w-lg">
        <DialogHeader>
          <DialogTitle>Edit Staff</DialogTitle>
          <DialogDescription>
            Update the role, branch placement, and access state for this account.
          </DialogDescription>
        </DialogHeader>
        <form className="space-y-4" onSubmit={handleSubmit}>
          <div className="grid gap-4 sm:grid-cols-2">
            <div className="space-y-2">
              <Label htmlFor={`staff-edit-name-${staff.id}`}>Full name</Label>
              <Input
                id={`staff-edit-name-${staff.id}`}
                name="fullName"
                defaultValue={staff.fullName}
                required
              />
            </div>
            <div className="space-y-2">
              <Label htmlFor={`staff-edit-email-${staff.id}`}>Email</Label>
              <Input
                id={`staff-edit-email-${staff.id}`}
                name="email"
                type="email"
                defaultValue={staff.email}
                required
              />
            </div>
          </div>
          <div className="grid gap-4 sm:grid-cols-2">
            <div className="space-y-2">
              <Label>Role</Label>
              <Select value={role} onValueChange={(value) => setRole(value as StaffUserDoc["role"])}>
                <SelectTrigger className="w-full">
                  <SelectValue placeholder="Select role" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="CASHIER">{ROLE_LABELS.CASHIER}</SelectItem>
                  <SelectItem value="MANAGER">{ROLE_LABELS.MANAGER}</SelectItem>
                  <SelectItem value="MERCHANT_ADMIN">{ROLE_LABELS.MERCHANT_ADMIN}</SelectItem>
                </SelectContent>
              </Select>
            </div>
            <div className="space-y-2">
              <Label>Status</Label>
              <Select value={status} onValueChange={(value) => setStatus(value as StaffUserDoc["status"])}>
                <SelectTrigger className="w-full">
                  <SelectValue placeholder="Select status" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="INVITED">Invited</SelectItem>
                  <SelectItem value="ACTIVE">Active</SelectItem>
                  <SelectItem value="DISABLED">Disabled</SelectItem>
                </SelectContent>
              </Select>
            </div>
          </div>
          <div className="space-y-2">
            <Label>Primary branch</Label>
            {requiresBranchAssignment ? (
              <Select value={primaryBranchId} onValueChange={setPrimaryBranchId}>
                <SelectTrigger className="w-full">
                  <SelectValue placeholder="Select branch" />
                </SelectTrigger>
                <SelectContent>
                  {branches.map((branch) => (
                    <SelectItem key={branch.id} value={branch.id}>
                      {branch.name}
                    </SelectItem>
                  ))}
                </SelectContent>
              </Select>
            ) : (
              <div className="rounded-lg border border-border/70 bg-background px-3 py-2 text-sm text-muted-foreground">
                Business admins continue to inherit all current branch assignments in this tenant.
              </div>
            )}
          </div>
          {errorMessage ? (
            <Alert variant="destructive">
              <AlertTriangle />
              <AlertTitle>Staff update failed</AlertTitle>
              <AlertDescription>{errorMessage}</AlertDescription>
            </Alert>
          ) : null}
          <DialogFooter>
            <Button
              type="submit"
              className="rounded-full"
              disabled={pending || (requiresBranchAssignment && branches.length === 0)}
            >
              {pending ? "Saving..." : "Save Staff"}
            </Button>
          </DialogFooter>
        </form>
      </DialogContent>
    </Dialog>
  );
}
