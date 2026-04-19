import Link from "next/link";
import {
  ArrowLeftRight,
  History,
  ShieldAlert,
  Ticket,
  TicketPlus,
  UserRoundSearch,
} from "lucide-react";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { SetupCallout } from "@/components/setup-callout";
import { getPassHistory } from "@/lib/server/loyalty-service";

function formatTimestamp(value: string) {
  return new Intl.DateTimeFormat("en-IN", {
    dateStyle: "medium",
    timeStyle: "short",
  }).format(new Date(value));
}

function getEventPresentation(eventType: string) {
  switch (eventType) {
    case "PURCHASE_ADDED":
      return {
        title: "Purchase added",
        description: "A qualifying purchase was recorded on this membership.",
        icon: TicketPlus,
      };
    case "REWARD_UNLOCKED":
      return {
        title: "Reward unlocked",
        description: "This membership crossed its threshold and earned reward credit.",
        icon: History,
      };
    case "REWARD_REDEEMED":
      return {
        title: "Reward redeemed",
        description: "A live redeem flow consumed one reward credit.",
        icon: Ticket,
      };
    case "PURCHASE_REVERSED":
      return {
        title: "Purchase reversed",
        description: "Store management reversed the most recent purchase correction.",
        icon: ArrowLeftRight,
      };
    case "REWARD_REDEMPTION_REVERSED":
      return {
        title: "Redemption reversed",
        description: "Store management restored a previously redeemed reward credit.",
        icon: ShieldAlert,
      };
    case "PASS_REISSUED":
      return {
        title: "Pass reissued",
        description: "A newer member pass replaced the previous pass version.",
        icon: UserRoundSearch,
      };
    default:
      return {
        title: eventType.replaceAll("_", " "),
        description: "Recorded in the immutable loyalty ledger.",
        icon: History,
      };
  }
}

export default async function PassHistoryPage({
  params,
}: {
  params: Promise<{ token: string }>;
}) {
  const { token } = await params;
  let pass:
    | Awaited<ReturnType<typeof getPassHistory>>
    | null = null;
  let errorMessage: string | null = null;

  try {
    pass = await getPassHistory(token);
  } catch (error) {
    errorMessage =
      error instanceof Error ? error.message : "Unable to load history.";
  }

  if (!pass) {
    return (
      <main className="container-edge flex min-h-screen items-center justify-center py-16">
        <div className="w-full max-w-2xl">
          <SetupCallout
            title="Member history unavailable"
            message={errorMessage ?? "Unable to load history."}
          />
        </div>
      </main>
    );
  }

  return (
    <main className="container-edge min-h-screen space-y-6 py-10">
      <div className="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div className="space-y-3">
          <Badge className="rounded-full px-3 py-1 tracking-[0.22em]">
            Member history
          </Badge>
          <div className="space-y-2">
            <h1 className="text-4xl font-semibold tracking-tight">Activity timeline</h1>
            <p className="text-lg text-muted-foreground">
              {pass.memberName} • {pass.planName} • {pass.maskedPhone}
            </p>
          </div>
        </div>
        <Button asChild variant="outline" className="rounded-full">
          <Link href={`/pass/${token}`}>Back to member pass</Link>
        </Button>
      </div>

      <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        <Card className="border-border/70 bg-card/90">
          <CardHeader>
            <CardTitle>Purchase count</CardTitle>
          </CardHeader>
          <CardContent className="text-3xl font-semibold tracking-tight">
            {pass.purchaseCount}
          </CardContent>
        </Card>
        <Card className="border-border/70 bg-card/90">
          <CardHeader>
            <CardTitle>Reward balance</CardTitle>
          </CardHeader>
          <CardContent className="text-3xl font-semibold tracking-tight">
            {pass.rewardBalance}
          </CardContent>
        </Card>
        <Card className="border-border/70 bg-card/90">
          <CardHeader>
            <CardTitle>Ledger entries</CardTitle>
          </CardHeader>
          <CardContent className="text-3xl font-semibold tracking-tight">
            {pass.entries.length}
          </CardContent>
        </Card>
      </div>

      <Card className="border-border/70 bg-card/90">
        <CardHeader>
          <CardTitle>Ledger-backed history</CardTitle>
        </CardHeader>
        <CardContent className="space-y-3">
          {pass.entries.length === 0 ? (
            <div className="rounded-2xl border border-dashed border-border/80 bg-background/60 p-4 text-sm leading-6 text-muted-foreground">
              No loyalty events have been recorded for this membership yet.
            </div>
          ) : (
            pass.entries.map((entry) => {
              const presentation = getEventPresentation(entry.eventType);

              return (
                <div
                  key={entry.id}
                  className="rounded-2xl border border-border/70 bg-background/70 p-4"
                >
                  <div className="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div className="space-y-2">
                      <div className="flex items-center gap-2">
                        <presentation.icon className="size-4 text-primary" />
                        <p className="font-medium">{presentation.title}</p>
                        <Badge variant="outline" className="rounded-full">
                          {entry.quantity}
                        </Badge>
                      </div>
                      <p className="text-sm leading-6 text-muted-foreground">
                        {presentation.description}
                      </p>
                    </div>
                    <p className="text-sm text-muted-foreground">
                      {formatTimestamp(entry.createdAt)}
                    </p>
                  </div>
                  <div className="mt-3 flex flex-wrap gap-2 text-xs uppercase tracking-[0.18em] text-muted-foreground">
                    <span>{entry.branchName ?? "Platform"}</span>
                    <span>•</span>
                    <span>{entry.source.replaceAll("_", " ")}</span>
                    {entry.reasonCode ? (
                      <>
                        <span>•</span>
                        <span>{entry.reasonCode}</span>
                      </>
                    ) : null}
                  </div>
                </div>
              );
            })
          )}
        </CardContent>
      </Card>
    </main>
  );
}
