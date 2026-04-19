"use client";

import { useEffect, useState, useTransition } from "react";
import {
  Crown,
  RefreshCcw,
  Search,
  ShieldAlert,
  TicketPlus,
  UserRoundSearch,
  Users,
  WifiOff,
} from "lucide-react";
import { DashboardStatusBadge } from "@/components/admin/dashboard-primitives";
import { Alert, AlertDescription, AlertTitle } from "@/components/ui/alert";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Separator } from "@/components/ui/separator";
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs";
import { Textarea } from "@/components/ui/textarea";
import { getStaffRoleDisplayName } from "@/lib/auth/role-labels";
import type { StaffRole } from "@/lib/firebase/model";
import { parseScanPayload } from "@/lib/qr";
import {
  enqueuePurchaseAdd,
  getQueuedPurchaseAdds,
} from "@/lib/offline/purchase-queue";
import { PurchaseQueueSync } from "@/components/staff/purchase-queue-sync";

type BranchSummary = {
  id: string;
  code: string;
  name: string;
  timezone: string;
};

type MemberListItem = {
  membershipId: string;
  branchId: string;
  customerName: string;
  maskedPhone: string;
  planName: string;
  summary: {
    purchaseCount: number;
    rewardBalance: number;
  };
};

type WorkspaceMembership = MemberListItem & {
  branchName: string;
  thresholdCount: number;
  rewardCreditCount: number;
  status: string;
  activePassId: string | null;
  activePassVersion: number | null;
  joinedAt: string | null;
  summary: MemberListItem["summary"] & {
    rewardRedeemedCount: number;
    lastActivityAt: string | null;
  };
};

type MembershipApiRecord = {
  membershipId: string;
  branchId: string;
  customerName: string;
  maskedPhone: string;
  planName: string;
  thresholdCount?: number;
  rewardCreditCount?: number;
  status?: string;
  activePassId?: string | null;
  activePassVersion?: number | null;
  summary: {
    purchaseCount: number;
    rewardBalance: number;
    rewardRedeemedCount?: number;
    lastActivityAt?: string | null;
  };
};

type TeamMember = {
  id: string;
  fullName: string;
  email: string;
  role: StaffRole;
  status: string;
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
};

type BranchPerformance = {
  id: string;
  code: string;
  name: string;
  activeMembers: number;
  purchaseAdds: number;
  rewardsRedeemed: number;
  totalActions: number;
  staffCount: number;
  managerCount: number;
  counterStaffCount: number;
  lastActivityAt: string | null;
};

type IdempotentPayload<T> = {
  replayed: boolean;
  payload: T;
};

type RequestPayload<T> = {
  ok: boolean;
  data?: T;
  error?: {
    message?: string;
  };
};

type StaffConsoleProps = {
  role: StaffRole;
  accessibleBranches: BranchSummary[];
  activeMembershipCount: number;
  recentMemberships: WorkspaceMembership[];
  branchPerformance: BranchPerformance[];
  teamMembers: TeamMember[];
};

function formatTimestamp(value: string | null) {
  if (!value) {
    return "No activity yet";
  }

  return new Intl.DateTimeFormat("en-IN", {
    dateStyle: "medium",
    timeStyle: "short",
  }).format(new Date(value));
}

function sortWorkspaceMembers(members: WorkspaceMembership[]) {
  return [...members].sort((left, right) => {
    const leftTime = left.summary.lastActivityAt ?? left.joinedAt ?? "";
    const rightTime = right.summary.lastActivityAt ?? right.joinedAt ?? "";
    return rightTime.localeCompare(leftTime);
  });
}

async function requestJson<T>(input: RequestInfo, init?: RequestInit) {
  const response = await fetch(input, init);
  const payload = (await response.json().catch(() => null)) as RequestPayload<T> | null;

  if (!response.ok || !payload?.ok || payload.data === undefined) {
    throw new Error(payload?.error?.message || "Request failed.");
  }

  return payload.data;
}

function WorkspaceStat({
  label,
  value,
  description,
}: {
  label: string;
  value: number | string;
  description: string;
}) {
  return (
    <div className="rounded-2xl border border-border/70 bg-card/90 p-4 shadow-sm">
      <p className="text-xs uppercase tracking-[0.22em] text-muted-foreground">
        {label}
      </p>
      <p className="mt-3 text-3xl font-semibold tracking-tight">{value}</p>
      <p className="mt-2 text-sm leading-6 text-muted-foreground">{description}</p>
    </div>
  );
}

function MemberListCard({
  member,
  isActive,
  onOpen,
  onUseForMerge,
}: {
  member: MemberListItem;
  isActive: boolean;
  onOpen: () => void;
  onUseForMerge?: (() => void) | null;
}) {
  return (
    <div className="rounded-2xl border border-border/70 bg-background/60 p-4">
      <div className="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div className="space-y-1">
          <p className="font-medium">{member.customerName}</p>
          <p className="text-sm text-muted-foreground">
            {member.maskedPhone} • {member.planName}
          </p>
          <p className="text-xs uppercase tracking-[0.18em] text-muted-foreground">
            Purchases {member.summary.purchaseCount} • Balance {member.summary.rewardBalance}
          </p>
        </div>
        <div className="flex flex-wrap gap-2">
          <Button
            type="button"
            variant={isActive ? "secondary" : "outline"}
            className="rounded-full"
            onClick={onOpen}
          >
            {isActive ? "Selected" : "Open member"}
          </Button>
          {onUseForMerge ? (
            <Button
              type="button"
              variant="outline"
              className="rounded-full"
              onClick={onUseForMerge}
            >
              Use for merge
            </Button>
          ) : null}
        </div>
      </div>
    </div>
  );
}

export function StaffConsole({
  role,
  accessibleBranches,
  activeMembershipCount,
  recentMemberships,
  branchPerformance,
  teamMembers,
}: StaffConsoleProps) {
  const [isPending, startTransition] = useTransition();
  const [scanPayload, setScanPayload] = useState("");
  const [phone, setPhone] = useState("");
  const [searchResults, setSearchResults] = useState<MemberListItem[]>([]);
  const [memberFeed, setMemberFeed] = useState<WorkspaceMembership[]>(recentMemberships);
  const [activeMembership, setActiveMembership] = useState<WorkspaceMembership | null>(
    recentMemberships[0] ?? null,
  );
  const [mergeCandidate, setMergeCandidate] = useState<MemberListItem | null>(null);
  const [status, setStatus] = useState<string | null>(
    recentMemberships[0]
      ? `Loaded ${recentMemberships[0].customerName} from recent active members.`
      : null,
  );
  const [error, setError] = useState<string | null>(null);
  const [queuedCount, setQueuedCount] = useState(0);
  const [isOnline, setIsOnline] = useState(
    typeof window === "undefined" ? true : window.navigator.onLine,
  );
  const [activeMutation, setActiveMutation] = useState<string | null>(null);
  const [pendingTeamStatusId, setPendingTeamStatusId] = useState<string | null>(null);
  const [teamStatusOverrides, setTeamStatusOverrides] = useState<
    Partial<Record<string, TeamMember["status"]>>
  >({});
  const [recoveryReasonCode, setRecoveryReasonCode] = useState("customer-present");
  const [verificationNote, setVerificationNote] = useState("");
  const [reissueReasonCode, setReissueReasonCode] = useState("lost-device");
  const [reversePurchaseReasonCode, setReversePurchaseReasonCode] =
    useState("counter-correction");
  const [reverseRedemptionReasonCode, setReverseRedemptionReasonCode] =
    useState("counter-correction");
  const [mergeReasonCode, setMergeReasonCode] = useState("duplicate-account");

  const isManagerSurface = role !== "CASHIER";

  useEffect(() => {
    const handleOnline = () => setIsOnline(true);
    const handleOffline = () => setIsOnline(false);

    void refreshQueueCount();
    window.addEventListener("online", handleOnline);
    window.addEventListener("offline", handleOffline);

    return () => {
      window.removeEventListener("online", handleOnline);
      window.removeEventListener("offline", handleOffline);
    };
  }, []);

  function getBranchName(branchId: string) {
    return accessibleBranches.find((branch) => branch.id === branchId)?.name ?? branchId;
  }

  function normalizeMembershipRecord(
    membership: MembershipApiRecord,
    previous?: WorkspaceMembership | null,
  ) {
    return {
      membershipId: membership.membershipId,
      branchId: membership.branchId,
      branchName: getBranchName(membership.branchId),
      customerName: membership.customerName,
      maskedPhone: membership.maskedPhone,
      planName: membership.planName,
      thresholdCount: membership.thresholdCount ?? previous?.thresholdCount ?? 0,
      rewardCreditCount: membership.rewardCreditCount ?? previous?.rewardCreditCount ?? 0,
      status: membership.status ?? previous?.status ?? "ACTIVE",
      activePassId: membership.activePassId ?? previous?.activePassId ?? null,
      activePassVersion:
        membership.activePassVersion ?? previous?.activePassVersion ?? null,
      joinedAt: previous?.joinedAt ?? null,
      summary: {
        purchaseCount: membership.summary.purchaseCount,
        rewardBalance: membership.summary.rewardBalance,
        rewardRedeemedCount:
          membership.summary.rewardRedeemedCount ??
          previous?.summary.rewardRedeemedCount ??
          0,
        lastActivityAt:
          membership.summary.lastActivityAt ?? previous?.summary.lastActivityAt ?? null,
      },
    } satisfies WorkspaceMembership;
  }

  function upsertMember(nextMember: WorkspaceMembership) {
    setMemberFeed((current) =>
      sortWorkspaceMembers([
        nextMember,
        ...current.filter((member) => member.membershipId !== nextMember.membershipId),
      ]).slice(0, 8),
    );
  }

  async function refreshQueueCount() {
    const items = await getQueuedPurchaseAdds();
    setQueuedCount(items.length);
  }

  async function handleTeamMemberStatusChange(
    staffUserId: string,
    nextStatus: "ACTIVE" | "DISABLED",
  ) {
    setPendingTeamStatusId(staffUserId);
    await runMutation("team-status", async () => {
      const updated = await requestJson<{ id: string; fullName: string; status: string }>(
        `/api/v1/staff-users/${staffUserId}`,
        {
          method: "PATCH",
          headers: {
            "Content-Type": "application/json",
          },
          body: JSON.stringify({ status: nextStatus }),
        },
      );

      setTeamStatusOverrides((current) => ({
        ...current,
        [updated.id]: updated.status as TeamMember["status"],
      }));
      setStatus(
        `${updated.fullName} is now ${updated.status === "DISABLED" ? "disabled" : "active"}.`,
      );
    });
    setPendingTeamStatusId(null);
  }

  async function resolveMembershipById(
    membershipId: string,
    successMessage?: string,
  ) {
    const previous =
      activeMembership?.membershipId === membershipId
        ? activeMembership
        : memberFeed.find((member) => member.membershipId === membershipId) ?? null;
    const membership = await requestJson<MembershipApiRecord>(
      `/api/v1/memberships/${membershipId}/summary`,
    );
    const nextMember = normalizeMembershipRecord(membership, previous);
    setActiveMembership(nextMember);
    upsertMember(nextMember);

    if (mergeCandidate?.membershipId === membershipId) {
      setMergeCandidate({
        membershipId: nextMember.membershipId,
        branchId: nextMember.branchId,
        customerName: nextMember.customerName,
        maskedPhone: nextMember.maskedPhone,
        planName: nextMember.planName,
        summary: {
          purchaseCount: nextMember.summary.purchaseCount,
          rewardBalance: nextMember.summary.rewardBalance,
        },
      });
    }

    setStatus(successMessage ?? `${nextMember.customerName} is ready at the counter.`);
    setError(null);
  }

  async function runMutation(
    mutationKey: string,
    run: () => Promise<void>,
  ) {
    setActiveMutation(mutationKey);
    setStatus(null);
    setError(null);

    try {
      await run();
    } catch (mutationError) {
      setError(
        mutationError instanceof Error ? mutationError.message : "Action failed.",
      );
    } finally {
      setActiveMutation(null);
    }
  }

  async function addPurchase(source: "QR_SCAN" | "PHONE_LOOKUP") {
    if (!activeMembership) {
      setError("Select a member before adding a purchase.");
      return;
    }

    const payload = {
      branchId: activeMembership.branchId,
      quantity: 1,
      source,
    };
    const idempotencyKey = crypto.randomUUID();

    if (!isOnline) {
      await enqueuePurchaseAdd({
        id: crypto.randomUUID(),
        membershipId: activeMembership.membershipId,
        branchId: activeMembership.branchId,
        idempotencyKey,
        createdAt: new Date().toISOString(),
        payload,
      });
      await refreshQueueCount();
      setStatus("Offline detected. Purchase add queued for replay.");
      setError(null);
      return;
    }

    await runMutation("purchase-add", async () => {
      const result = await requestJson<
        IdempotentPayload<{
          membershipId: string;
          purchaseCount: number;
          rewardBalance: number;
          unlockedRewardCredits: number;
        }>
      >(`/api/v1/memberships/${activeMembership.membershipId}/purchase-add`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "Idempotency-Key": idempotencyKey,
        },
        body: JSON.stringify(payload),
      });

      const nextMember: WorkspaceMembership = {
        ...activeMembership,
        summary: {
          ...activeMembership.summary,
          purchaseCount: result.payload.purchaseCount,
          rewardBalance: result.payload.rewardBalance,
          lastActivityAt: new Date().toISOString(),
        },
      };
      setActiveMembership(nextMember);
      upsertMember(nextMember);
      setStatus(
        result.payload.unlockedRewardCredits > 0
          ? `Purchase recorded. ${result.payload.unlockedRewardCredits} reward credit(s) unlocked.`
          : "Purchase recorded.",
      );
    });
  }

  async function handleResolvePayload() {
    setStatus(null);
    setError(null);

    const parsed = parseScanPayload(scanPayload);
    if (parsed.type === "UNKNOWN") {
      setError("The scanner could not classify this payload.");
      return;
    }

    if (parsed.type === "PASS") {
      const membership = await requestJson<MembershipApiRecord>(
        "/api/v1/memberships/lookup-by-qr",
        {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
          },
          body: JSON.stringify({ qrPayload: scanPayload }),
        },
      );
      const nextMember = normalizeMembershipRecord(membership);
      setActiveMembership(nextMember);
      upsertMember(nextMember);
      setStatus(`${nextMember.customerName} resolved from pass QR.`);
      return;
    }

    if (!isOnline) {
      setError("Redemption requires a live server confirmation.");
      return;
    }

    const redemption = await requestJson<
      IdempotentPayload<{ membershipId: string; rewardBalance: number }>
    >("/api/v1/redemptions/consume", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "Idempotency-Key": crypto.randomUUID(),
      },
      body: JSON.stringify({ redeemToken: parsed.token }),
    });

    await resolveMembershipById(
      redemption.payload.membershipId,
      `Reward redeemed. Remaining balance: ${redemption.payload.rewardBalance}.`,
    );
  }

  async function handlePhoneSearch() {
    setStatus(null);
    setError(null);
    const results = await requestJson<MemberListItem[]>(
      `/api/v1/memberships/search?phone=${encodeURIComponent(phone)}`,
    );
    setSearchResults(results);

    if (results.length === 1) {
      await resolveMembershipById(
        results[0].membershipId,
        `${results[0].customerName} resolved from phone search.`,
      );
      return;
    }

    setStatus(
      results.length === 0
        ? "No active members matched this phone number."
        : `${results.length} member(s) matched this phone number.`,
    );
  }

  async function runManagerAction(
    mutationKey: string,
    endpoint: string,
    body: Record<string, string>,
    successMessage: string,
    refreshMembershipId?: string,
  ) {
    await runMutation(mutationKey, async () => {
      await requestJson<IdempotentPayload<Record<string, unknown>>>(endpoint, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "Idempotency-Key": crypto.randomUUID(),
        },
        body: JSON.stringify(body),
      });

      if (refreshMembershipId) {
        await resolveMembershipById(refreshMembershipId, successMessage);
      } else {
        setStatus(successMessage);
      }
    });
  }

  const visibleTeamMembers = teamMembers.map((member) => ({
    ...member,
    status: teamStatusOverrides[member.id] ?? member.status,
  }));

  return (
    <div className="space-y-6">
      <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <WorkspaceStat
          label="Active members"
          value={activeMembershipCount}
          description="Current active memberships visible across your assigned branches."
        />
        <WorkspaceStat
          label="Branch access"
          value={accessibleBranches.length}
          description="Branch scope enforced server-side for every lookup and write."
        />
        <WorkspaceStat
          label="Offline queue"
          value={queuedCount}
          description="Only purchase-add operations can queue offline for replay."
        />
        <WorkspaceStat
          label="Role surface"
          value={getStaffRoleDisplayName(role)}
          description={
            isManagerSurface
              ? "Store managers and business admins see counter-staff tools plus recovery, reversal, reissue, and merge controls."
              : "Counter staff can identify members, add purchases, and redeem only with live tokens."
          }
        />
      </div>

      <div className="grid gap-6 xl:grid-cols-[1.05fr_0.95fr]">
        <div className="space-y-6">
          <PurchaseQueueSync />

          <Card className="border-border/70 bg-card/90">
            <CardHeader>
              <CardTitle>Branch visibility</CardTitle>
              <CardDescription>
                These are the branches this signed-in account can operate.
              </CardDescription>
            </CardHeader>
            <CardContent className="flex flex-wrap gap-2">
              {accessibleBranches.map((branch) => (
                <Badge key={branch.id} variant="outline" className="rounded-full px-3 py-1">
                  {branch.name} • {branch.code}
                </Badge>
              ))}
            </CardContent>
          </Card>

          {isManagerSurface ? (
            <>
              <Card className="border-border/70 bg-card/90">
                <CardHeader>
                  <CardTitle>Store performance</CardTitle>
                  <CardDescription>
                    Branch-level activity and member volume for the branches in your current scope.
                  </CardDescription>
                </CardHeader>
                <CardContent className="space-y-3">
                  {branchPerformance.length === 0 ? (
                    <div className="rounded-2xl border border-dashed border-border/80 bg-background/60 p-4 text-sm leading-6 text-muted-foreground">
                      Store-level metrics will appear here after memberships or staff activity are recorded.
                    </div>
                  ) : (
                    branchPerformance.map((branch) => (
                      <div
                        key={branch.id}
                        className="rounded-2xl border border-border/70 bg-background/60 p-4"
                      >
                        <div className="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                          <div className="space-y-1">
                            <p className="font-medium">{branch.name}</p>
                            <p className="text-sm text-muted-foreground">
                              {branch.code} • Last activity {formatTimestamp(branch.lastActivityAt)}
                            </p>
                          </div>
                          <Badge variant="outline" className="rounded-full">
                            {branch.activeMembers} active members
                          </Badge>
                        </div>
                        <div className="mt-3 grid gap-3 sm:grid-cols-4">
                          <div className="rounded-2xl border border-border/70 bg-card/80 p-3">
                            <p className="text-xs uppercase tracking-[0.18em] text-muted-foreground">
                              Team
                            </p>
                            <p className="mt-2 text-xl font-semibold">{branch.staffCount}</p>
                          </div>
                          <div className="rounded-2xl border border-border/70 bg-card/80 p-3">
                            <p className="text-xs uppercase tracking-[0.18em] text-muted-foreground">
                              Purchases
                            </p>
                            <p className="mt-2 text-xl font-semibold">{branch.purchaseAdds}</p>
                          </div>
                          <div className="rounded-2xl border border-border/70 bg-card/80 p-3">
                            <p className="text-xs uppercase tracking-[0.18em] text-muted-foreground">
                              Rewards
                            </p>
                            <p className="mt-2 text-xl font-semibold">{branch.rewardsRedeemed}</p>
                          </div>
                          <div className="rounded-2xl border border-border/70 bg-card/80 p-3">
                            <p className="text-xs uppercase tracking-[0.18em] text-muted-foreground">
                              Managers
                            </p>
                            <p className="mt-2 text-xl font-semibold">{branch.managerCount}</p>
                          </div>
                        </div>
                      </div>
                    ))
                  )}
                </CardContent>
              </Card>

              <Card className="border-border/70 bg-card/90">
                <CardHeader>
                  <CardTitle>Team coverage</CardTitle>
                  <CardDescription>
                    Review staff performance and manage the accounts you are allowed to control.
                  </CardDescription>
                </CardHeader>
                <CardContent className="space-y-3">
                  {visibleTeamMembers.length === 0 ? (
                    <div className="rounded-2xl border border-dashed border-border/80 bg-background/60 p-4 text-sm leading-6 text-muted-foreground">
                      No team members are visible for this role yet.
                    </div>
                  ) : (
                    visibleTeamMembers.map((staffMember) => {
                      const shouldActivate = staffMember.status !== "ACTIVE";
                      const nextStatus = shouldActivate ? "ACTIVE" : "DISABLED";
                      const actionLabel = shouldActivate ? "Activate" : "Disable";

                      return (
                        <div
                          key={staffMember.id}
                          className="rounded-2xl border border-border/70 bg-background/60 p-4"
                        >
                          <div className="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                            <div className="space-y-2">
                              <div className="flex flex-wrap items-center gap-2">
                                <p className="font-medium">{staffMember.fullName}</p>
                                <Badge variant="outline" className="rounded-full">
                                  {getStaffRoleDisplayName(staffMember.role)}
                                </Badge>
                                <DashboardStatusBadge value={staffMember.status} />
                              </div>
                              <p className="text-sm text-muted-foreground">
                                {staffMember.email}
                              </p>
                              <p className="text-sm text-muted-foreground">
                                {staffMember.branchNames.length === 0
                                  ? "No branch assignments"
                                  : staffMember.branchNames.join(", ")}
                              </p>
                            </div>
                            {staffMember.canManageStatus ? (
                              <Button
                                type="button"
                                variant={nextStatus === "DISABLED" ? "outline" : "default"}
                                className="rounded-full"
                                disabled={activeMutation === "team-status"}
                                onClick={() =>
                                  void handleTeamMemberStatusChange(
                                    staffMember.id,
                                    nextStatus,
                                  )
                                }
                              >
                                {activeMutation === "team-status" &&
                                pendingTeamStatusId === staffMember.id
                                  ? "Saving..."
                                  : actionLabel}
                              </Button>
                            ) : (
                              <Badge variant="secondary" className="rounded-full">
                                {staffMember.isCurrentUser ? "Current account" : "Read only"}
                              </Badge>
                            )}
                          </div>
                          <div className="mt-3 grid gap-3 sm:grid-cols-4">
                            <div className="rounded-2xl border border-border/70 bg-card/80 p-3">
                              <p className="text-xs uppercase tracking-[0.18em] text-muted-foreground">
                                Purchases
                              </p>
                              <p className="mt-2 text-xl font-semibold">{staffMember.purchaseAdds}</p>
                            </div>
                            <div className="rounded-2xl border border-border/70 bg-card/80 p-3">
                              <p className="text-xs uppercase tracking-[0.18em] text-muted-foreground">
                                Rewards
                              </p>
                              <p className="mt-2 text-xl font-semibold">{staffMember.rewardsRedeemed}</p>
                            </div>
                            <div className="rounded-2xl border border-border/70 bg-card/80 p-3">
                              <p className="text-xs uppercase tracking-[0.18em] text-muted-foreground">
                                Reversals
                              </p>
                              <p className="mt-2 text-xl font-semibold">{staffMember.reversals}</p>
                            </div>
                            <div className="rounded-2xl border border-border/70 bg-card/80 p-3">
                              <p className="text-xs uppercase tracking-[0.18em] text-muted-foreground">
                                Last activity
                              </p>
                              <p className="mt-2 text-sm font-medium">
                                {formatTimestamp(staffMember.lastActionAt)}
                              </p>
                            </div>
                          </div>
                        </div>
                      );
                    })
                  )}
                </CardContent>
              </Card>
            </>
          ) : null}

          <Tabs defaultValue="scan" className="space-y-4">
            <TabsList className="grid w-full grid-cols-2 rounded-full">
              <TabsTrigger value="scan">Scan / Paste payload</TabsTrigger>
              <TabsTrigger value="phone">Phone fallback</TabsTrigger>
            </TabsList>

            <TabsContent value="scan">
              <Card className="border-border/70 bg-card/90">
                <CardHeader>
                  <CardTitle>Resolve pass or live redeem token</CardTitle>
                  <CardDescription>
                    Pass lookup opens the member. Redeem-token scan consumes a live reward.
                  </CardDescription>
                </CardHeader>
                <CardContent className="space-y-4">
                  <Label htmlFor="scanPayload">QR payload</Label>
                  <Textarea
                    id="scanPayload"
                    value={scanPayload}
                    onChange={(event) => setScanPayload(event.target.value)}
                    placeholder="Paste a pass URL or LOYALTY_REDEEM token payload"
                    className="min-h-32"
                  />
                  <Button
                    disabled={isPending}
                    className="rounded-full"
                    onClick={() => startTransition(() => void handleResolvePayload())}
                  >
                    Resolve payload
                  </Button>
                </CardContent>
              </Card>
            </TabsContent>

            <TabsContent value="phone">
              <Card className="border-border/70 bg-card/90">
                <CardHeader>
                  <CardTitle>Phone fallback</CardTitle>
                  <CardDescription>
                    Search active members by phone for earning or store-manager review.
                  </CardDescription>
                </CardHeader>
                <CardContent className="space-y-4">
                  <div className="flex gap-3">
                    <Input
                      value={phone}
                      onChange={(event) => setPhone(event.target.value)}
                      placeholder="+91 98765 43210"
                    />
                    <Button
                      type="button"
                      variant="outline"
                      onClick={() => startTransition(() => void handlePhoneSearch())}
                    >
                      <Search className="size-4" />
                    </Button>
                  </div>

                  <div className="space-y-3">
                    {searchResults.map((result) => (
                      <MemberListCard
                        key={result.membershipId}
                        member={result}
                        isActive={activeMembership?.membershipId === result.membershipId}
                        onOpen={() =>
                          startTransition(() =>
                            void resolveMembershipById(
                              result.membershipId,
                              `${result.customerName} resolved from phone search.`,
                            ),
                          )
                        }
                        onUseForMerge={
                          isManagerSurface &&
                          activeMembership &&
                          activeMembership.membershipId !== result.membershipId
                            ? () => {
                                setMergeCandidate(result);
                                setStatus(
                                  `${result.customerName} is ready to merge into ${activeMembership.customerName}.`,
                                );
                                setError(null);
                              }
                            : null
                        }
                      />
                    ))}
                  </div>
                </CardContent>
              </Card>
            </TabsContent>
          </Tabs>

          <Card className="border-border/70 bg-card/90">
            <CardHeader>
              <CardTitle>Recent active members</CardTitle>
              <CardDescription>
                Open members who recently joined or were recently active in your branch scope.
              </CardDescription>
            </CardHeader>
            <CardContent className="space-y-3">
              {memberFeed.length === 0 ? (
                <div className="rounded-2xl border border-dashed border-border/80 bg-background/60 p-4 text-sm leading-6 text-muted-foreground">
                  No active members are visible for this account yet.
                </div>
              ) : (
                memberFeed.map((member) => (
                  <div
                    key={member.membershipId}
                    className="rounded-2xl border border-border/70 bg-background/60 p-4"
                  >
                    <div className="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                      <div className="space-y-1">
                        <p className="font-medium">{member.customerName}</p>
                        <p className="text-sm text-muted-foreground">
                          {member.maskedPhone} • {member.planName}
                        </p>
                        <p className="text-xs uppercase tracking-[0.18em] text-muted-foreground">
                          {member.branchName} • Last activity{" "}
                          {formatTimestamp(member.summary.lastActivityAt)}
                        </p>
                      </div>
                      <div className="flex flex-wrap gap-2">
                        <Button
                          type="button"
                          variant={
                            activeMembership?.membershipId === member.membershipId
                              ? "secondary"
                              : "outline"
                          }
                          className="rounded-full"
                          onClick={() => setActiveMembership(member)}
                        >
                          {activeMembership?.membershipId === member.membershipId
                            ? "Selected"
                            : "Open member"}
                        </Button>
                      </div>
                    </div>
                  </div>
                ))
              )}
            </CardContent>
          </Card>
        </div>

        <div className="space-y-6">
          <Card className="border-border/70 bg-card/90">
            <CardHeader>
              <CardTitle>Selected member</CardTitle>
              <CardDescription>
                Counter staff, store managers, and business admins all operate from this member detail panel.
              </CardDescription>
            </CardHeader>
            <CardContent className="space-y-5">
              {activeMembership ? (
                <>
                  <div className="space-y-2">
                    <div className="flex flex-wrap items-center gap-2">
                      <p className="text-2xl font-semibold tracking-tight">
                        {activeMembership.customerName}
                      </p>
                      <Badge variant="outline" className="rounded-full">
                        {activeMembership.status}
                      </Badge>
                    </div>
                    <p className="text-sm leading-6 text-muted-foreground">
                      {activeMembership.planName} • {activeMembership.maskedPhone} •{" "}
                      {activeMembership.branchName}
                    </p>
                  </div>

                  <div className="grid gap-3 md:grid-cols-2">
                    <div className="rounded-2xl border border-border/70 bg-background/70 p-4">
                      <p className="text-xs uppercase tracking-[0.2em] text-muted-foreground">
                        Purchases
                      </p>
                      <p className="mt-2 text-2xl font-semibold">
                        {activeMembership.summary.purchaseCount}
                      </p>
                    </div>
                    <div className="rounded-2xl border border-border/70 bg-background/70 p-4">
                      <p className="text-xs uppercase tracking-[0.2em] text-muted-foreground">
                        Reward balance
                      </p>
                      <p className="mt-2 text-2xl font-semibold">
                        {activeMembership.summary.rewardBalance}
                      </p>
                    </div>
                    <div className="rounded-2xl border border-border/70 bg-background/70 p-4">
                      <p className="text-xs uppercase tracking-[0.2em] text-muted-foreground">
                        Threshold
                      </p>
                      <p className="mt-2 text-2xl font-semibold">
                        {activeMembership.thresholdCount} → {activeMembership.rewardCreditCount}
                      </p>
                    </div>
                    <div className="rounded-2xl border border-border/70 bg-background/70 p-4">
                      <p className="text-xs uppercase tracking-[0.2em] text-muted-foreground">
                        Last activity
                      </p>
                      <p className="mt-2 text-sm font-medium">
                        {formatTimestamp(activeMembership.summary.lastActivityAt)}
                      </p>
                    </div>
                  </div>

                  <div className="flex flex-wrap gap-3">
                    <Button
                      onClick={() =>
                        void addPurchase(searchResults.length > 0 ? "PHONE_LOOKUP" : "QR_SCAN")
                      }
                      className="rounded-full"
                      disabled={activeMutation !== null}
                    >
                      <TicketPlus />
                      Add purchase
                    </Button>
                    <Button
                      variant="outline"
                      className="rounded-full"
                      disabled={isPending}
                      onClick={() =>
                        startTransition(() =>
                          void resolveMembershipById(
                            activeMembership.membershipId,
                            `${activeMembership.customerName} refreshed.`,
                          ),
                        )
                      }
                    >
                      <RefreshCcw />
                      Refresh member
                    </Button>
                    {!isOnline ? (
                      <div className="inline-flex items-center gap-2 rounded-full border border-amber-500/30 bg-amber-500/10 px-4 py-2 text-sm text-amber-950">
                        <WifiOff className="size-4" />
                        Offline mode active
                      </div>
                    ) : null}
                  </div>

                  <Separator />

                  <div className="space-y-2 text-sm leading-6 text-muted-foreground">
                    <p>
                      Membership ID:{" "}
                      <span className="font-mono text-foreground">
                        {activeMembership.membershipId}
                      </span>
                    </p>
                    <p>
                      Active pass:{" "}
                      <span className="font-mono text-foreground">
                        {activeMembership.activePassId ?? "No active pass"}
                      </span>
                    </p>
                  </div>
                </>
              ) : (
                <div className="rounded-2xl border border-dashed border-border/80 bg-background/60 p-4 text-sm leading-6 text-muted-foreground">
                  Scan a pass, search by phone, or open a recent member to start operating.
                </div>
              )}
            </CardContent>
          </Card>

          {isManagerSurface && activeMembership ? (
            <Card className="border-border/70 bg-card/90">
              <CardHeader>
                <CardTitle>Advanced controls</CardTitle>
                <CardDescription>
                  Store managers and business admins inherit counter-staff controls and can also recover redemptions, reissue passes, reverse mistakes, and merge duplicates.
                </CardDescription>
              </CardHeader>
              <CardContent className="space-y-5">
                <div className="grid gap-4 md:grid-cols-2">
                  <div className="rounded-2xl border border-border/70 bg-background/70 p-4">
                    <div className="flex items-center gap-2">
                      <ShieldAlert className="size-4 text-primary" />
                      <p className="font-medium">Phone recovery redemption</p>
                    </div>
                    <div className="mt-3 space-y-3">
                      <div className="space-y-2">
                        <Label htmlFor="recoveryReasonCode">Reason code</Label>
                        <Input
                          id="recoveryReasonCode"
                          value={recoveryReasonCode}
                          onChange={(event) => setRecoveryReasonCode(event.target.value)}
                        />
                      </div>
                      <div className="space-y-2">
                        <Label htmlFor="verificationNote">Verification note</Label>
                        <Textarea
                          id="verificationNote"
                          value={verificationNote}
                          onChange={(event) => setVerificationNote(event.target.value)}
                          className="min-h-24"
                        />
                      </div>
                      <Button
                        className="w-full rounded-full"
                        disabled={activeMutation !== null || verificationNote.trim().length < 4}
                        onClick={() =>
                          void runManagerAction(
                            "redeem-recovery",
                            `/api/v1/memberships/${activeMembership.membershipId}/redeem`,
                            {
                              reasonCode: recoveryReasonCode,
                              verificationNote,
                            },
                            "Recovery redemption completed.",
                            activeMembership.membershipId,
                          )
                        }
                      >
                        <Crown />
                        Recover redemption
                      </Button>
                    </div>
                  </div>

                  <div className="rounded-2xl border border-border/70 bg-background/70 p-4">
                    <div className="flex items-center gap-2">
                      <UserRoundSearch className="size-4 text-primary" />
                      <p className="font-medium">Reissue member pass</p>
                    </div>
                    <div className="mt-3 space-y-3">
                      <div className="space-y-2">
                        <Label htmlFor="reissueReasonCode">Reason code</Label>
                        <Input
                          id="reissueReasonCode"
                          value={reissueReasonCode}
                          onChange={(event) => setReissueReasonCode(event.target.value)}
                        />
                      </div>
                      <Button
                        className="w-full rounded-full"
                        disabled={activeMutation !== null}
                        onClick={() =>
                          void runManagerAction(
                            "pass-reissue",
                            `/api/v1/memberships/${activeMembership.membershipId}/reissue-pass`,
                            {
                              reasonCode: reissueReasonCode,
                            },
                            "Pass reissued. Any earlier pass and live redeem token were revoked.",
                            activeMembership.membershipId,
                          )
                        }
                      >
                        Reissue pass
                      </Button>
                    </div>
                  </div>

                  <div className="rounded-2xl border border-border/70 bg-background/70 p-4">
                    <div className="flex items-center gap-2">
                      <TicketPlus className="size-4 text-primary" />
                      <p className="font-medium">Reverse last purchase</p>
                    </div>
                    <div className="mt-3 space-y-3">
                      <div className="space-y-2">
                        <Label htmlFor="reversePurchaseReasonCode">Reason code</Label>
                        <Input
                          id="reversePurchaseReasonCode"
                          value={reversePurchaseReasonCode}
                          onChange={(event) => setReversePurchaseReasonCode(event.target.value)}
                        />
                      </div>
                      <Button
                        variant="outline"
                        className="w-full rounded-full"
                        disabled={activeMutation !== null}
                        onClick={() =>
                          void runManagerAction(
                            "reverse-purchase",
                            `/api/v1/memberships/${activeMembership.membershipId}/reverse-purchase`,
                            {
                              reasonCode: reversePurchaseReasonCode,
                            },
                            "Latest purchase reversed.",
                            activeMembership.membershipId,
                          )
                        }
                      >
                        Reverse purchase
                      </Button>
                    </div>
                  </div>

                  <div className="rounded-2xl border border-border/70 bg-background/70 p-4">
                    <div className="flex items-center gap-2">
                      <RefreshCcw className="size-4 text-primary" />
                      <p className="font-medium">Reverse last redemption</p>
                    </div>
                    <div className="mt-3 space-y-3">
                      <div className="space-y-2">
                        <Label htmlFor="reverseRedemptionReasonCode">Reason code</Label>
                        <Input
                          id="reverseRedemptionReasonCode"
                          value={reverseRedemptionReasonCode}
                          onChange={(event) =>
                            setReverseRedemptionReasonCode(event.target.value)
                          }
                        />
                      </div>
                      <Button
                        variant="outline"
                        className="w-full rounded-full"
                        disabled={activeMutation !== null}
                        onClick={() =>
                          void runManagerAction(
                            "reverse-redemption",
                            `/api/v1/memberships/${activeMembership.membershipId}/reverse-redeem`,
                            {
                              reasonCode: reverseRedemptionReasonCode,
                            },
                            "Latest redemption reversed.",
                            activeMembership.membershipId,
                          )
                        }
                      >
                        Reverse redemption
                      </Button>
                    </div>
                  </div>
                </div>

                <div className="rounded-2xl border border-border/70 bg-background/70 p-4">
                  <div className="flex items-center gap-2">
                    <Users className="size-4 text-primary" />
                    <p className="font-medium">Merge duplicate memberships</p>
                  </div>
                  <div className="mt-3 space-y-3">
                    <p className="text-sm leading-6 text-muted-foreground">
                      Use phone search to pick the duplicate membership, then merge it
                      into the currently selected member.
                    </p>
                    {mergeCandidate ? (
                      <div className="rounded-2xl border border-border/70 bg-card/80 p-3">
                        <p className="font-medium">{mergeCandidate.customerName}</p>
                        <p className="text-sm text-muted-foreground">
                          {mergeCandidate.maskedPhone} • {mergeCandidate.planName}
                        </p>
                        <p className="mt-2 text-xs uppercase tracking-[0.18em] text-muted-foreground">
                          Membership {mergeCandidate.membershipId}
                        </p>
                      </div>
                    ) : (
                      <div className="rounded-2xl border border-dashed border-border/80 bg-card/50 p-3 text-sm text-muted-foreground">
                        No merge source selected yet.
                      </div>
                    )}
                    <div className="space-y-2">
                      <Label htmlFor="mergeReasonCode">Reason code</Label>
                      <Input
                        id="mergeReasonCode"
                        value={mergeReasonCode}
                        onChange={(event) => setMergeReasonCode(event.target.value)}
                      />
                    </div>
                    <div className="flex flex-wrap gap-3">
                      <Button
                        className="rounded-full"
                        disabled={activeMutation !== null || !mergeCandidate}
                        onClick={() =>
                          void runManagerAction(
                            "membership-merge",
                            "/api/v1/memberships/merge",
                            {
                              survivorMembershipId: activeMembership.membershipId,
                              obsoleteMembershipId: mergeCandidate?.membershipId ?? "",
                              reasonCode: mergeReasonCode,
                            },
                            "Memberships merged. The selected member is now canonical.",
                            activeMembership.membershipId,
                          ).then(() => {
                            setMergeCandidate(null);
                          })
                        }
                      >
                        Merge into selected member
                      </Button>
                      {mergeCandidate ? (
                        <Button
                          variant="outline"
                          className="rounded-full"
                          disabled={activeMutation !== null}
                          onClick={() => setMergeCandidate(null)}
                        >
                          Clear merge source
                        </Button>
                      ) : null}
                    </div>
                  </div>
                </div>
              </CardContent>
            </Card>
          ) : null}

          {status ? (
            <Alert>
              <AlertTitle>Workspace update</AlertTitle>
              <AlertDescription>{status}</AlertDescription>
            </Alert>
          ) : null}

          {error ? (
            <Alert variant="destructive">
              <AlertTitle>Action failed</AlertTitle>
              <AlertDescription>{error}</AlertDescription>
            </Alert>
          ) : null}
        </div>
      </div>
    </div>
  );
}
