import { redirect } from "next/navigation";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";

function firstValue(value: string | string[] | undefined) {
  return Array.isArray(value) ? value[0] : value;
}

function resolveBranchCode(value: string | string[] | undefined) {
  const normalizedValue = firstValue(value)?.trim();
  if (!normalizedValue) {
    return null;
  }

  const relativeMatch = normalizedValue.match(/^\/join\/([^/?#]+)/i);
  if (relativeMatch?.[1]) {
    return decodeURIComponent(relativeMatch[1]).trim();
  }

  try {
    const url = new URL(normalizedValue);
    const absoluteMatch = url.pathname.match(/^\/join\/([^/?#]+)/i);
    if (absoluteMatch?.[1]) {
      return decodeURIComponent(absoluteMatch[1]).trim();
    }
  } catch {
    // Treat the value as a plain branch code when it is not a full URL.
  }

  return normalizedValue;
}

export default async function JoinLandingPage({
  searchParams,
}: {
  searchParams: Promise<{ branchCode?: string | string[] }>;
}) {
  const { branchCode } = await searchParams;
  const normalizedBranchCode = resolveBranchCode(branchCode);

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
            Open your store's join link and start membership in under a minute.
          </h1>
          <p className="max-w-2xl text-lg leading-8 text-muted-foreground">
            Every branch has its own unique join link. Paste the full branch link
            or the short join code shared by the store to open the correct enrollment page.
          </p>
        </div>
      </section>

      <Card className="border-border/70 bg-card/90 shadow-xl shadow-sky-950/5">
        <CardHeader>
          <CardTitle>Open branch enrollment</CardTitle>
          <CardDescription>
            Use the full branch link from the store QR, or enter the short join code shared by staff.
          </CardDescription>
        </CardHeader>
        <CardContent>
          <form className="space-y-4" action="/join">
            <div className="space-y-2">
              <Label htmlFor="branch-code">Branch link or join code</Label>
              <Input
                id="branch-code"
                name="branchCode"
                placeholder="https://arrzone.vercel.app/join/downtown-store or downtown-store"
                autoComplete="off"
                required
              />
            </div>
            <p className="text-sm leading-6 text-muted-foreground">
              If you landed here from the public homepage, ask staff for the branch join link. Members should not need to guess the branch code.
            </p>
            <Button type="submit" className="w-full rounded-full">
              Open enrollment
            </Button>
          </form>
        </CardContent>
      </Card>
    </main>
  );
}
