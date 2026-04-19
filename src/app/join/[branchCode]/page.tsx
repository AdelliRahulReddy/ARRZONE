import { notFound } from "next/navigation";
import { Badge } from "@/components/ui/badge";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { EnrollmentForm } from "@/components/join/enrollment-form";
import { SetupCallout } from "@/components/setup-callout";
import { ROLE_LABELS } from "@/lib/auth/role-labels";
import { appEnv } from "@/lib/env";
import { AppError } from "@/lib/server/errors";
import { getEnrollmentBranchContext } from "@/lib/server/loyalty-service";

export default async function JoinPage({
  params,
}: {
  params: Promise<{ branchCode: string }>;
}) {
  const { branchCode } = await params;

  if (!branchCode) {
    notFound();
  }

  let enrollmentContext: Awaited<ReturnType<typeof getEnrollmentBranchContext>>;

  try {
    enrollmentContext = await getEnrollmentBranchContext(branchCode);
  } catch (error) {
    if (error instanceof AppError && error.code === "BRANCH_NOT_FOUND") {
      notFound();
    }

    throw error;
  }

  const { branch, plans } = enrollmentContext;

  return (
    <main className="container-edge grid min-h-screen gap-10 py-10 lg:grid-cols-[0.95fr_1.05fr] lg:items-start">
      <section className="space-y-6">
        <Badge className="rounded-full px-3 py-1 tracking-[0.22em]">
          Branch QR enrollment
        </Badge>
        <div className="space-y-4">
          <h1 className="text-4xl font-semibold tracking-tight text-balance md:text-5xl">
            Become a loyalty member in under a minute.
          </h1>
          <p className="max-w-xl text-lg leading-8 text-muted-foreground">
            This QR-first flow creates the {ROLE_LABELS.MEMBER.toLowerCase()} record, membership, consent log,
            and pass in one transaction. The pass link stays long-lived, while any
            actual redemption must use a live 60-second redeem token.
          </p>
        </div>
        {!appEnv.hasFirebaseAdmin ? (
          <SetupCallout
            title="Firebase setup required"
            message="Enrollment requires Firebase Admin credentials plus an active branch and plan configuration before members can complete this flow."
          />
        ) : null}
        <Card className="border-border/70 bg-card/85">
          <CardHeader>
            <CardTitle>Branch context</CardTitle>
          </CardHeader>
          <CardContent className="space-y-2 text-sm leading-6 text-muted-foreground">
            <p>
              Branch: <span className="font-medium text-foreground">{branch.name}</span>
            </p>
            <p>
              Branch code: <span className="font-medium text-foreground">{branch.code}</span>
            </p>
            <p>
              Available plans:{" "}
              <span className="font-medium text-foreground">{plans.length}</span>
            </p>
            <p>
              Plan selection is backed by merchant-defined plan versions and now
              loads automatically for this branch.
            </p>
          </CardContent>
        </Card>
      </section>
      <EnrollmentForm branchCode={branchCode} plans={plans} />
    </main>
  );
}
