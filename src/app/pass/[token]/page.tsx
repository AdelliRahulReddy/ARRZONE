import Link from "next/link";
import { AlertCircle, ShieldCheck, Ticket } from "lucide-react";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { QrCodeBox } from "@/components/qr-code-box";
import { RedeemTokenPanel } from "@/components/pass/redeem-token-panel";
import { SetupCallout } from "@/components/setup-callout";
import { ROLE_LABELS } from "@/lib/auth/role-labels";
import { getPassSnapshot } from "@/lib/server/loyalty-service";

export default async function PassPage({
  params,
}: {
  params: Promise<{ token: string }>;
}) {
  const { token } = await params;
  let pass:
    | Awaited<ReturnType<typeof getPassSnapshot>>
    | null = null;
  let errorMessage: string | null = null;

  try {
    pass = await getPassSnapshot(token);
  } catch (error) {
    errorMessage =
      error instanceof Error ? error.message : "The pass could not be loaded.";
  }

  if (!pass) {
    return (
      <main className="container-edge flex min-h-screen items-center justify-center py-16">
        <div className="w-full max-w-2xl">
          <SetupCallout
            title="Member pass unavailable"
            message={errorMessage ?? "The pass could not be loaded."}
            actionHref="/"
            actionLabel="Back to overview"
          />
        </div>
      </main>
    );
  }

  return (
    <main className="container-edge grid min-h-screen gap-10 py-10 lg:grid-cols-[1fr_0.95fr]">
      <section className="space-y-6">
        <div className="space-y-3">
          <Badge className="rounded-full px-3 py-1 tracking-[0.22em]">
            {ROLE_LABELS.MEMBER} pass
          </Badge>
          <h1 className="text-4xl font-semibold tracking-tight text-balance">
            {pass.customerName}
          </h1>
          <p className="text-lg text-muted-foreground">
            {pass.planName} • {pass.maskedPhone}
          </p>
        </div>
        <Card className="border-border/70 bg-card/90">
          <CardHeader>
            <CardTitle>Pass details</CardTitle>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="grid gap-4 md:grid-cols-3">
              {[
                {
                  label: "Purchases",
                  value: pass.summary.purchaseCount,
                  icon: Ticket,
                },
                {
                  label: "Reward balance",
                  value: pass.summary.rewardBalance,
                  icon: ShieldCheck,
                },
                {
                  label: "Threshold",
                  value: `${pass.thresholdCount} -> ${pass.rewardCreditCount}`,
                  icon: AlertCircle,
                },
              ].map((item) => (
                <div
                  key={item.label}
                  className="rounded-2xl border border-border/70 bg-background/70 p-4"
                >
                  <div className="flex items-center justify-between">
                    <p className="text-xs uppercase tracking-[0.2em] text-muted-foreground">
                      {item.label}
                    </p>
                    <item.icon className="size-4 text-primary" />
                  </div>
                  <p className="mt-3 text-2xl font-semibold">{item.value}</p>
                </div>
              ))}
            </div>
            <Button asChild variant="outline" className="rounded-full">
              <Link href={`/pass/${token}/history`}>Open member history</Link>
            </Button>
          </CardContent>
        </Card>
        <Card className="border-border/70 bg-card/90">
          <CardHeader>
            <CardTitle>Static pass QR</CardTitle>
          </CardHeader>
          <CardContent className="space-y-4">
            <QrCodeBox
              value={pass.passUrl}
              label="This QR only opens the pass and supports earning lookup. It cannot redeem rewards."
            />
          </CardContent>
        </Card>
      </section>
      <section className="space-y-6">
        <RedeemTokenPanel passToken={token} />
        <Card className="border-border/70 bg-card/90">
          <CardHeader>
            <CardTitle>Security model</CardTitle>
          </CardHeader>
          <CardContent className="space-y-3 text-sm leading-6 text-muted-foreground">
            <p>
              Reissuing the pass revokes every earlier pass version and any outstanding
              redeem token.
            </p>
            <p>
              Counter staff can use the pass QR for earning, but redemption requires a
              fresh server-issued redeem token or a store-manager recovery action.
            </p>
          </CardContent>
        </Card>
      </section>
    </main>
  );
}
