"use client";

import { useState, useTransition } from "react";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { QrCodeBox } from "@/components/qr-code-box";

export function RedeemTokenPanel({ passToken }: { passToken: string }) {
  const [isPending, startTransition] = useTransition();
  const [redeemPayload, setRedeemPayload] = useState<string | null>(null);
  const [expiresAt, setExpiresAt] = useState<string | null>(null);
  const [error, setError] = useState<string | null>(null);

  return (
    <Card className="border-border/70 bg-card/90">
      <CardHeader className="space-y-3">
        <CardTitle>Redeem token</CardTitle>
        <p className="text-sm leading-6 text-muted-foreground">
          Static pass QR only opens the pass. Generate a short-lived redeem QR right
          before checkout. Every new token revokes the previous one.
        </p>
      </CardHeader>
      <CardContent className="space-y-4">
        <Button
          type="button"
          disabled={isPending}
          onClick={() =>
            startTransition(async () => {
              setError(null);
              const response = await fetch(
                `/api/v1/passes/${encodeURIComponent(passToken)}/redeem-token`,
                { method: "POST" },
              );
              const payload = (await response.json()) as {
                ok: boolean;
                data?: { redeemQrPayload: string; expiresAt: string };
                error?: { message: string };
              };

              if (!response.ok || !payload.ok || !payload.data) {
                setError(payload.error?.message ?? "Could not generate a redeem token.");
                return;
              }

              setRedeemPayload(payload.data.redeemQrPayload);
              setExpiresAt(payload.data.expiresAt);
            })
          }
          className="rounded-full"
        >
          {isPending ? "Generating..." : "Generate live redeem QR"}
        </Button>
        {error ? <p className="text-sm text-destructive">{error}</p> : null}
        {redeemPayload ? (
          <>
            <QrCodeBox
              value={redeemPayload}
              label="This QR expires in 60 seconds and can only be consumed once."
            />
            <p className="text-sm text-muted-foreground">
              Expires at {expiresAt ? new Date(expiresAt).toLocaleTimeString() : "--"}.
            </p>
          </>
        ) : null}
      </CardContent>
    </Card>
  );
}
