import Link from "next/link";
import {
  ArrowRight,
  Building2,
  QrCode,
  ShieldCheck,
  Smartphone,
  Store,
} from "lucide-react";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { BUSINESS_ADMIN_ROUTE } from "@/lib/auth/constants";
import { ROLE_LABELS } from "@/lib/auth/role-labels";

export default function Home() {
  return (
    <main className="mx-auto flex min-h-screen w-full max-w-7xl flex-col gap-16 px-6 py-10 md:px-10">
      <section className="grid gap-10 lg:grid-cols-[1.25fr_0.75fr] lg:items-end">
        <div className="space-y-6">
          <Badge className="rounded-full border border-border/70 bg-background/70 px-3 py-1 text-xs uppercase tracking-[0.24em] text-muted-foreground shadow-sm backdrop-blur">
            QR Loyalty OS
          </Badge>
          <div className="space-y-4">
            <h1 className="max-w-4xl text-5xl font-semibold tracking-tight text-balance md:text-7xl">
              Run a checkout-speed loyalty program without an app, OTP, or paper cards.
            </h1>
            <p className="max-w-2xl text-lg leading-8 text-muted-foreground md:text-xl">
              Launch branch-based loyalty enrollment, counter-safe redemption, and
              role-scoped admin operations with signed pass links, short-lived redeem
              QR tokens, immutable ledger events, and server-enforced tenant isolation.
            </p>
          </div>
          <div className="flex flex-col gap-3 sm:flex-row">
            <Button asChild size="lg" className="h-12 rounded-full px-6">
              <Link href="/join">
                Open Member Enrollment
                <ArrowRight className="size-4" />
              </Link>
            </Button>
            <Button
              asChild
              size="lg"
              variant="outline"
              className="h-12 rounded-full px-6"
            >
              <Link href="/staff">Open Store Operations</Link>
            </Button>
            <Button
              asChild
              size="lg"
              variant="outline"
              className="h-12 rounded-full px-6"
            >
              <Link href={BUSINESS_ADMIN_ROUTE}>Open Business Admin</Link>
            </Button>
          </div>
        </div>
        <Card className="border-border/70 bg-card/80 shadow-xl shadow-sky-950/5 backdrop-blur">
          <CardHeader>
            <CardTitle className="text-sm uppercase tracking-[0.24em] text-muted-foreground">
              Application Surfaces
            </CardTitle>
          </CardHeader>
          <CardContent className="grid gap-4">
            {[
              {
                href: "/join",
                icon: Smartphone,
                title: `${ROLE_LABELS.MEMBER} Enrollment`,
                copy: "Branch-based enrollment, pass issuance, and live redeem-token generation.",
              },
              {
                href: "/staff",
                icon: QrCode,
                title: "Store Operations",
                copy: "Lookup, purchase add, redeem consume, and offline purchase queue.",
              },
              {
                href: BUSINESS_ADMIN_ROUTE,
                icon: Building2,
                title: ROLE_LABELS.MERCHANT_ADMIN,
                copy: "Plans, branches, staff roles, security exceptions, and reports.",
              },
              {
                href: "/platform",
                icon: ShieldCheck,
                title: ROLE_LABELS.PLATFORM_ADMIN,
                copy: "Multi-tenant oversight, Firestore policy hygiene, and suspicious activity review.",
              },
            ].map((item) => (
              <Link
                key={item.title}
                href={item.href}
                className="group rounded-2xl border border-border/70 bg-background/70 p-4 transition hover:border-primary/40 hover:bg-background"
              >
                <div className="flex items-start gap-4">
                  <div className="rounded-2xl bg-primary/10 p-3 text-primary">
                    <item.icon className="size-5" />
                  </div>
                  <div className="space-y-1">
                    <p className="font-medium">{item.title}</p>
                    <p className="text-sm leading-6 text-muted-foreground">
                      {item.copy}
                    </p>
                  </div>
                </div>
              </Link>
            ))}
          </CardContent>
        </Card>
      </section>

      <section className="grid gap-4 md:grid-cols-3">
        {[
          {
            icon: Store,
            title: "Counter-safe redemption",
            copy: "Static pass QR is never redeemable. Only a live, short-lived redeem token can be consumed.",
          },
          {
            icon: ShieldCheck,
            title: "Server-enforced isolation",
            copy: "Privileged writes stay behind Next.js APIs, Firestore rules deny direct client access, and transaction guards enforce the invariants.",
          },
          {
            icon: QrCode,
            title: "Offline kept narrow",
            copy: "Only purchase-add is queueable. Redemption and corrective actions require live server confirmation.",
          },
        ].map((item) => (
          <Card key={item.title} className="border-border/70 bg-card/75 shadow-sm">
            <CardContent className="flex h-full flex-col gap-4 p-6">
              <div className="w-fit rounded-2xl border border-border/70 bg-background p-3">
                <item.icon className="size-5 text-primary" />
              </div>
              <div className="space-y-2">
                <h2 className="text-lg font-semibold">{item.title}</h2>
                <p className="text-sm leading-6 text-muted-foreground">
                  {item.copy}
                </p>
              </div>
            </CardContent>
          </Card>
        ))}
      </section>
    </main>
  );
}
