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
import {
  DashboardEmptyState,
  DashboardStatusBadge,
} from "@/components/admin/dashboard-primitives";
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
import { cn } from "@/lib/utils";
import { PurchaseQueueSync } from "@/components/staff/purchase-queue-sync";
import { StaffQrCameraScanner } from "@/components/staff/qr-camera-scanner";

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

type LookupMode = "scan" | "phone";

const consoleCardClass =
  "overflow-hidden rounded-[1.8rem] border border-border/60 bg-card/95 shadow-[0_24px_70px_-42px_rgba(15,23,42,0.18)]";
const consoleInsetClass =
  "rounded-[1.35rem] border border-border/60 bg-background/75 p-4 shadow-[inset_0_1px_0_rgba(255,255,255,0.72)]";
const consoleStatClass =
  "rounded-[1.25rem] border border-border/60 bg-background/80 p-4 shadow-[inset_0_1px_0_rgba(255,255,255,0.72)]";

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
    <div className="rounded-[1.7rem] border border-border/60 bg-card/95 p-4 shadow-[0_22px_60px_-42px_rgba(15,23,42,0.18)]">
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
    <div className="rounded-[1.4rem] border border-border/60 bg-background/72 p-4 shadow-[inset_0_1px_0_rgba(255,255,255,0.72)]">
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
            className="w-full rounded-full sm:w-auto"
            onClick={onOpen}
          >
            {isActive ? "Selected" : "Open member"}
          </Button>
          {onUseForMerge ? (
            <Button
              type="button"
              variant="outline"
              className="w-full rounded-full sm:w-auto"
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
  const [activeLookupMode, setActiveLookupMode] = useState<LookupMode>("scan");
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

  async function handleResolvePayload(rawOverride?: string) {
    setStatus(null);
    setError(null);

    const resolvedPayload = (rawOverride ?? scanPayload).trim();
    const parsed = parseScanPayload(resolvedPayload);
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
          body: JSON.stringify({ qrPayload: resolvedPayload }),
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

  function handleCameraDetected(rawValue: string) {
    const normalizedPayload = rawValue.trim();
    if (!normalizedPayload) {
      return;
    }

    setScanPayload(normalizedPayload);
    setError(null);
    startTransition(() => void handleResolvePayload(normalizedPayload));
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
    <div className="space-y-8">
      <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <WorkspaceStat
          label="Active members"
          value={activeMembershipCount}
          description="Current active memberships visible across your assigned branches."
        />
        <WorkspaceStat
          label="Branch access"
          value={accessibleBranches.length}
          description="Branches this signed-in account can operate."
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

      <PurchaseQueueSync />

      <div className="grid gap-6 xl:grid-cols-[minmax(0,1.02fr)_minmax(0,0.98fr)]">
        <Card className={consoleCardClass}>
          <CardHeader className="space-y-5">
            <div className="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
              <div className="space-y-2">
                <CardTitle>Counter workspace</CardTitle>
                <CardDescription>
                  Resolve a member first, then keep checkout, recovery, and merge actions anchored beside the selected profile.
                </CardDescription>
              </div>
              <div className="flex flex-wrap gap-2">
                <Badge variant="outline" className="rounded-full px-3 py-1">
                  {getStaffRoleDisplayName(role)}
                </Badge>
                <Badge variant="outline" className="rounded-full px-3 py-1">
                  {accessibleBranches.length} branch
                  {accessibleBranches.length === 1 ? "" : "es"}
                </Badge>
                <Badge
                  variant="outline"
                  className={cn(
                    "rounded-full px-3 py-1",
                    isOnline
                      ? "border-emerald-500/30 bg-emerald-500/10 text-emerald-700"
                      : "border-amber-500/30 bg-amber-500/10 text-amber-900",
                  )}
                >
                  {isOnline ? "Online" : "Offline queueing"}
                </Badge>
              </div>
            </div>

            <div className="flex flex-wrap gap-2">
              {accessibleBranches.map((branch) => (
                <Badge key={branch.id} variant="outline" className="rounded-full px-3 py-1">
                  {branch.name} • {branch.code}
                </Badge>
              ))}
            </div>
          </CardHeader>

          <CardContent className="space-y-5">
            <Tabs
              value={activeLookupMode}
              onValueChange={(value) => setActiveLookupMode(value as LookupMode)}
              className="space-y-4"
            >
              <TabsList className="grid w-full grid-cols-2 rounded-[1rem] border border-border/60 bg-background/70 p-1 shadow-[inset_0_1px_0_rgba(255,255,255,0.72)]">
                <TabsTrigger value="scan" className="rounded-[0.85rem]">
                  Camera scanner
                </TabsTrigger>
                <TabsTrigger value="phone" className="rounded-[0.85rem]">
                  Phone search
                </TabsTrigger>
              </TabsList>

              <TabsContent value="scan">
                <div className={cn(consoleInsetClass, "space-y-4")}>
                  <div className="space-y-1">
                    <p className="font-medium">Scan member pass or redeem token</p>
                    <p className="text-sm leading-6 text-muted-foreground">
                      Use the live scanner first. If camera capture is unreliable, paste a pass URL, pass token, or redeem token manually.
                    </p>
                  </div>

                  <StaffQrCameraScanner
                    active={activeLookupMode === "scan" && !isPending}
                    onDetected={handleCameraDetected}
                  />

                  <Separator />

                  <div className="space-y-2">
                    <Label htmlFor="scanPayload">Manual QR payload</Label>
                    <Textarea
                      id="scanPayload"
                      value={scanPayload}
                      onChange={(event) => setScanPayload(event.target.value)}
                      placeholder="Paste a full pass URL, /pass/... path, raw pass token, or LOYALTY_REDEEM token"
                      className="min-h-32 rounded-[1rem]"
                    />
                  </div>
                  <Button
                    disabled={isPending || !scanPayload.trim()}
                    className="w-full rounded-full sm:w-auto"
                    onClick={() => startTransition(() => void handleResolvePayload())}
                  >
                    Resolve pasted payload
                  </Button>
                </div>
              </TabsContent>

              <TabsContent value="phone">
                <div className={cn(consoleInsetClass, "space-y-4")}>
                  <div className="space-y-1">
                    <p className="font-medium">Phone search</p>
                    <p className="text-sm leading-6 text-muted-foreground">
                      Search active members by phone for earnings, exception review, or duplicate-account merge prep.
                    </p>
                  </div>

                  <div className="flex flex-col gap-3 sm:flex-row">
                    <Input
                      value={phone}
                      onChange={(event) => setPhone(event.target.value)}
                      placeholder="+91 98765 43210"
                      className="rounded-[1rem]"
                    />
                    <Button
                      type="button"
                      variant="outline"
                      className="rounded-full sm:w-auto"
                      onClick={() => startTransition(() => void handlePhoneSearch())}
                    >
                      <Search className="size-4" />
                      Search
                    </Button>
                  </div>

                  <div className="space-y-3">
                    {searchResults.length === 0 ? (
                      <DashboardEmptyState
                        title="Search results appear here"
                        description="Search by phone to open a member directly or select a duplicate profile for merge."
                      />
                    ) : (
                      searchResults.map((result) => (
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
                      ))
                    )}
                  </div>
                </div>
              </TabsContent>
            </Tabs>
          </CardContent>
        </Card>

        <div className="space-y-6">
          <Card className={cn(consoleCardClass, "xl:sticky xl:top-6")}>
            <CardHeader>
              <CardTitle>Selected member</CardTitle>
              <CardDescription>
                Keep the current member profile visible while you scan, search, add purchases, or resolve exceptions.
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
                      <DashboardStatusBadge value={activeMembership.status} />
                    </div>
                    <p className="text-sm leading-6 text-muted-foreground">
                      {activeMembership.planName} • {activeMembership.maskedPhone} •{" "}
                      {activeMembership.branchName}
                    </p>
                  </div>

                  <div className="grid gap-3 sm:grid-cols-2">
                    <div className={consoleStatClass}>
                      <p className="text-xs uppercase tracking-[0.2em] text-muted-foreground">
                        Purchases
                      </p>
                      <p className="mt-2 text-2xl font-semibold">
                        {activeMembership.summary.purchaseCount}
                      </p>
                    </div>
                    <div className={consoleStatClass}>
                      <p className="text-xs uppercase tracking-[0.2em] text-muted-foreground">
                        Reward balance
                      </p>
                      <p className="mt-2 text-2xl font-semibold">
                        {activeMembership.summary.rewardBalance}
                      </p>
                    </div>
                    <div className={consoleStatClass}>
                      <p className="text-xs uppercase tracking-[0.2em] text-muted-foreground">
                        Threshold
                      </p>
                      <p className="mt-2 text-2xl font-semibold">
                        {activeMembership.thresholdCount} → {activeMembership.rewardCreditCount}
                      </p>
                    </div>
                    <div className={consoleStatClass}>
                      <p className="text-xs uppercase tracking-[0.2em] text-muted-foreground">
                        Last activity
                      </p>
                      <p className="mt-2 text-sm font-medium">
                        {formatTimestamp(activeMembership.summary.lastActivityAt)}
                      </p>
                    </div>
                  </div>

                  <div className="flex flex-col gap-3 sm:flex-row sm:flex-wrap">
                    <Button
                      onClick={() =>
                        void addPurchase(searchResults.length > 0 ? "PHONE_LOOKUP" : "QR_SCAN")
                      }
                      className="rounded-full sm:w-auto"
                      disabled={activeMutation !== null}
                    >
                      <TicketPlus />
                      Add purchase
                    </Button>
                    <Button
                      variant="outline"
                      className="rounded-full sm:w-auto"
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
                      <div className="inline-flex items-center justify-center gap-2 rounded-full border border-amber-500/30 bg-amber-500/10 px-4 py-2 text-sm text-amber-950">
                        <WifiOff className="size-4" />
                        Offline mode active
                      </div>
                    ) : null}
                  </div>

                  <div className={cn(consoleInsetClass, "space-y-2 text-sm leading-6 text-muted-foreground")}>
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
                <DashboardEmptyState
                  title="No member selected yet"
                  description="Scan a pass, paste a payload, or search by phone to bring a member into the live counter workspace."
                />
              )}
            </CardContent>
          </Card>

          {status ? (
            <Alert className="rounded-[1.5rem] border border-border/60 bg-card/90 shadow-[0_18px_50px_-40px_rgba(15,23,42,0.18)]">
              <AlertTitle>Workspace update</AlertTitle>
              <AlertDescription>{status}</AlertDescription>
            </Alert>
          ) : null}

          {error ? (
            <Alert
              variant="destructive"
              className="rounded-[1.5rem] shadow-[0_18px_50px_-40px_rgba(15,23,42,0.18)]"
            >
              <AlertTitle>Action failed</AlertTitle>
              <AlertDescription>{error}</AlertDescription>
            </Alert>
          ) : null}
        </div>
      </div>

      {isManagerSurface && activeMembership ? (
        <Card className={consoleCardClass}>
          <CardHeader>
            <CardTitle>Advanced controls</CardTitle>
            <CardDescription>
              Managers inherit counter-staff tools and can also recover redemptions, reissue passes, reverse mistakes, and merge duplicates.
            </CardDescription>
          </CardHeader>
          <CardContent className="space-y-5">
            <div className="grid gap-4 xl:grid-cols-2">
              <div className={cn(consoleInsetClass, "space-y-3")}>
                <div className="flex items-center gap-2">
                  <ShieldAlert className="size-4 text-primary" />
                  <p className="font-medium">Phone recovery redemption</p>
                </div>
                <div className="space-y-2">
                  <Label htmlFor="recoveryReasonCode">Reason code</Label>
                  <Input
                    id="recoveryReasonCode"
                    value={recoveryReasonCode}
                    onChange={(event) => setRecoveryReasonCode(event.target.value)}
                    className="rounded-[1rem]"
                  />
                </div>
                <div className="space-y-2">
                  <Label htmlFor="verificationNote">Verification note</Label>
                  <Textarea
                    id="verificationNote"
                    value={verificationNote}
                    onChange={(event) => setVerificationNote(event.target.value)}
                    className="min-h-24 rounded-[1rem]"
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

              <div className={cn(consoleInsetClass, "space-y-3")}>
                <div className="flex items-center gap-2">
                  <UserRoundSearch className="size-4 text-primary" />
                  <p className="font-medium">Reissue member pass</p>
                </div>
                <div className="space-y-2">
                  <Label htmlFor="reissueReasonCode">Reason code</Label>
                  <Input
                    id="reissueReasonCode"
                    value={reissueReasonCode}
                    onChange={(event) => setReissueReasonCode(event.target.value)}
                    className="rounded-[1rem]"
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

              <div className={cn(consoleInsetClass, "space-y-3")}>
                <div className="flex items-center gap-2">
                  <TicketPlus className="size-4 text-primary" />
                  <p className="font-medium">Reverse last purchase</p>
                </div>
                <div className="space-y-2">
                  <Label htmlFor="reversePurchaseReasonCode">Reason code</Label>
                  <Input
                    id="reversePurchaseReasonCode"
                    value={reversePurchaseReasonCode}
                    onChange={(event) => setReversePurchaseReasonCode(event.target.value)}
                    className="rounded-[1rem]"
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

              <div className={cn(consoleInsetClass, "space-y-3")}>
                <div className="flex items-center gap-2">
                  <RefreshCcw className="size-4 text-primary" />
                  <p className="font-medium">Reverse last redemption</p>
                </div>
                <div className="space-y-2">
                  <Label htmlFor="reverseRedemptionReasonCode">Reason code</Label>
                  <Input
                    id="reverseRedemptionReasonCode"
                    value={reverseRedemptionReasonCode}
                    onChange={(event) =>
                      setReverseRedemptionReasonCode(event.target.value)
                    }
                    className="rounded-[1rem]"
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

            <div className={cn(consoleInsetClass, "space-y-3")}>
              <div className="flex items-center gap-2">
                <Users className="size-4 text-primary" />
                <p className="font-medium">Merge duplicate memberships</p>
              </div>
              <p className="text-sm leading-6 text-muted-foreground">
                Use phone search to choose the duplicate membership, then merge it into the currently selected member.
              </p>
              {mergeCandidate ? (
                <div className={consoleStatClass}>
                  <p className="font-medium">{mergeCandidate.customerName}</p>
                  <p className="text-sm text-muted-foreground">
                    {mergeCandidate.maskedPhone} • {mergeCandidate.planName}
                  </p>
                  <p className="mt-2 text-xs uppercase tracking-[0.18em] text-muted-foreground">
                    Membership {mergeCandidate.membershipId}
                  </p>
                </div>
              ) : (
                <DashboardEmptyState
                  title="No merge source selected"
                  description="Open the phone-search tab, find the duplicate member, and choose “Use for merge”."
                />
              )}
              <div className="space-y-2">
                <Label htmlFor="mergeReasonCode">Reason code</Label>
                <Input
                  id="mergeReasonCode"
                  value={mergeReasonCode}
                  onChange={(event) => setMergeReasonCode(event.target.value)}
                  className="rounded-[1rem]"
                />
              </div>
              <div className="flex flex-col gap-3 sm:flex-row sm:flex-wrap">
                <Button
                  className="rounded-full sm:w-auto"
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
                    className="rounded-full sm:w-auto"
                    disabled={activeMutation !== null}
                    onClick={() => setMergeCandidate(null)}
                  >
                    Clear merge source
                  </Button>
                ) : null}
              </div>
            </div>
          </CardContent>
        </Card>
      ) : null}

      <div
        className={cn(
          "grid gap-6",
          isManagerSurface
            ? "xl:grid-cols-[minmax(0,0.84fr)_minmax(0,1.16fr)]"
            : "xl:grid-cols-1",
        )}
      >
        <Card className={consoleCardClass}>
          <CardHeader>
            <CardTitle>Recent active members</CardTitle>
            <CardDescription>
              Open members who recently joined or were recently active inside your visible branch scope.
            </CardDescription>
          </CardHeader>
          <CardContent className="space-y-3">
            {memberFeed.length === 0 ? (
              <DashboardEmptyState
                title="No active members yet"
                description="Member activity will populate here once enrollments or counter events are recorded for your branch scope."
              />
            ) : (
              memberFeed.map((member) => (
                <div
                  key={member.membershipId}
                  className="rounded-[1.4rem] border border-border/60 bg-background/72 p-4 shadow-[inset_0_1px_0_rgba(255,255,255,0.72)]"
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
                        className="w-full rounded-full sm:w-auto"
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

        {isManagerSurface ? (
          <div className="space-y-6">
            <Card className={consoleCardClass}>
              <CardHeader>
                <CardTitle>Store performance</CardTitle>
                <CardDescription>
                  Branch-level activity and member volume for the branches visible to this account.
                </CardDescription>
              </CardHeader>
              <CardContent className="space-y-3">
                {branchPerformance.length === 0 ? (
                  <DashboardEmptyState
                    title="Store metrics will appear here"
                    description="Branch-level performance becomes visible after memberships or staff activity are recorded."
                  />
                ) : (
                  branchPerformance.map((branch) => (
                    <div key={branch.id} className={cn(consoleInsetClass, "space-y-4")}>
                      <div className="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                        <div className="space-y-1">
                          <p className="font-medium">{branch.name}</p>
                          <p className="text-sm text-muted-foreground">
                            {branch.code} • Last activity {formatTimestamp(branch.lastActivityAt)}
                          </p>
                        </div>
                        <Badge variant="outline" className="rounded-full px-3 py-1">
                          {branch.activeMembers} active members
                        </Badge>
                      </div>
                      <div className="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                        <div className={consoleStatClass}>
                          <p className="text-xs uppercase tracking-[0.18em] text-muted-foreground">
                            Team
                          </p>
                          <p className="mt-2 text-xl font-semibold">{branch.staffCount}</p>
                        </div>
                        <div className={consoleStatClass}>
                          <p className="text-xs uppercase tracking-[0.18em] text-muted-foreground">
                            Purchases
                          </p>
                          <p className="mt-2 text-xl font-semibold">{branch.purchaseAdds}</p>
                        </div>
                        <div className={consoleStatClass}>
                          <p className="text-xs uppercase tracking-[0.18em] text-muted-foreground">
                            Rewards
                          </p>
                          <p className="mt-2 text-xl font-semibold">{branch.rewardsRedeemed}</p>
                        </div>
                        <div className={consoleStatClass}>
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

            <Card className={consoleCardClass}>
              <CardHeader>
                <CardTitle>Team coverage</CardTitle>
                <CardDescription>
                  Review staff performance and change the account status where this role is allowed to manage access.
                </CardDescription>
              </CardHeader>
              <CardContent className="space-y-3">
                {visibleTeamMembers.length === 0 ? (
                  <DashboardEmptyState
                    title="No staff members are visible"
                    description="Team records will appear here once users are assigned into your current branch scope."
                  />
                ) : (
                  visibleTeamMembers.map((staffMember) => {
                    const shouldActivate = staffMember.status !== "ACTIVE";
                    const nextStatus = shouldActivate ? "ACTIVE" : "DISABLED";
                    const actionLabel = shouldActivate ? "Activate" : "Disable";

                    return (
                      <div key={staffMember.id} className={cn(consoleInsetClass, "space-y-4")}>
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
                              className="rounded-full sm:w-auto"
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
                        <div className="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                          <div className={consoleStatClass}>
                            <p className="text-xs uppercase tracking-[0.18em] text-muted-foreground">
                              Purchases
                            </p>
                            <p className="mt-2 text-xl font-semibold">
                              {staffMember.purchaseAdds}
                            </p>
                          </div>
                          <div className={consoleStatClass}>
                            <p className="text-xs uppercase tracking-[0.18em] text-muted-foreground">
                              Rewards
                            </p>
                            <p className="mt-2 text-xl font-semibold">
                              {staffMember.rewardsRedeemed}
                            </p>
                          </div>
                          <div className={consoleStatClass}>
                            <p className="text-xs uppercase tracking-[0.18em] text-muted-foreground">
                              Reversals
                            </p>
                            <p className="mt-2 text-xl font-semibold">{staffMember.reversals}</p>
                          </div>
                          <div className={consoleStatClass}>
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
          </div>
        ) : null}
      </div>
    </div>
  );
}
