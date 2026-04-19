import Link from "next/link";
import { Alert, AlertDescription, AlertTitle } from "@/components/ui/alert";
import { Button } from "@/components/ui/button";

type SetupCalloutProps = {
  title: string;
  message: string;
  actionHref?: string;
  actionLabel?: string;
};

export function SetupCallout({
  title,
  message,
  actionHref,
  actionLabel,
}: SetupCalloutProps) {
  return (
    <Alert className="border-amber-500/30 bg-amber-500/10 text-amber-950">
      <AlertTitle>{title}</AlertTitle>
      <AlertDescription className="mt-2 space-y-3 text-sm leading-6 text-amber-950/80">
        <p>{message}</p>
        {actionHref && actionLabel ? (
          <Button asChild size="sm" variant="secondary" className="rounded-full">
            <Link href={actionHref}>{actionLabel}</Link>
          </Button>
        ) : null}
      </AlertDescription>
    </Alert>
  );
}
