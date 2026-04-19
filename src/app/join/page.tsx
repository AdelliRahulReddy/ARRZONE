import { redirect } from "next/navigation";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";

export default async function JoinLandingPage({
  searchParams,
}: {
  searchParams: Promise<{ branchCode?: string }>;
}) {
  const { branchCode } = await searchParams;
  const normalizedBranchCode = branchCode?.trim();

  if (normalizedBranchCode) {
    redirect(`/join/${encodeURIComponent(normalizedBranchCode)}`);
  }

  return (
    <main className="container-edge grid min-h-screen gap-10 py-10 lg:grid-cols-[1fr_0.9fr] lg:items-center">
      <section className="space-y-6">
        <Badge className="rounded-full px-3 py-1 tracking-[0.22em]">
          Member enrollment
        </Badge>
        <div className="space-y-4">
          <h1 className="text-4xl font-semibold tracking-tight text-balance md:text-5xl">
            Start the loyalty join flow with your store branch code.
          </h1>
          <p className="max-w-2xl text-lg leading-8 text-muted-foreground">
            Members join from a branch QR or a branch-specific link. Enter the
            branch code provided by the store to open the correct enrollment page.
          </p>
        </div>
      </section>

      <Card className="border-border/70 bg-card/90 shadow-xl shadow-sky-950/5">
        <CardHeader>
          <CardTitle>Open branch enrollment</CardTitle>
          <CardDescription>
            Use the exact branch code printed on the in-store QR or shared by staff.
          </CardDescription>
        </CardHeader>
        <CardContent>
          <form className="space-y-4" action="/join">
            <div className="space-y-2">
              <Label htmlFor="branch-code">Branch code</Label>
              <Input
                id="branch-code"
                name="branchCode"
                placeholder="downtown-store"
                autoComplete="off"
                required
              />
            </div>
            <Button type="submit" className="w-full rounded-full">
              Open enrollment
            </Button>
          </form>
        </CardContent>
      </Card>
    </main>
  );
}
