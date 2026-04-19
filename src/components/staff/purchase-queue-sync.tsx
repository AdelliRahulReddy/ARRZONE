"use client";

import { useEffect, useEffectEvent, useState } from "react";
import {
  getQueuedPurchaseAdds,
  removeQueuedPurchaseAdd,
} from "@/lib/offline/purchase-queue";

export function PurchaseQueueSync() {
  const [queueCount, setQueueCount] = useState(0);
  const [lastError, setLastError] = useState<string | null>(null);

  const flushQueue = useEffectEvent(async () => {
    const items = await getQueuedPurchaseAdds();
    for (const item of items) {
      const response = await fetch(
        `/api/v1/memberships/${item.membershipId}/purchase-add`,
        {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            "Idempotency-Key": item.idempotencyKey,
          },
          body: JSON.stringify({
            branchId: item.branchId,
            quantity: item.payload.quantity,
            source: item.payload.source,
          }),
        },
      );

      if (response.ok) {
        await removeQueuedPurchaseAdd(item.id);
      } else {
        const payload = (await response.json().catch(() => null)) as
          | { error?: { message?: string } }
          | null;
        setLastError(payload?.error?.message ?? "Could not sync queued purchase.");
      }
    }

    const remaining = await getQueuedPurchaseAdds();
    setQueueCount(remaining.length);
  });

  useEffect(() => {
    let mounted = true;

    void getQueuedPurchaseAdds().then((items) => {
      if (mounted) {
        setQueueCount(items.length);
      }
    });

    return () => {
      mounted = false;
    };
  }, []);

  useEffect(() => {
    const onOnline = () => {
      void flushQueue();
    };

    window.addEventListener("online", onOnline);
    return () => {
      window.removeEventListener("online", onOnline);
    };
  }, []);

  if (queueCount === 0 && !lastError) {
    return null;
  }

  return (
    <div className="rounded-[1.6rem] border border-amber-500/30 bg-amber-500/10 p-4 text-sm text-amber-950 shadow-[0_18px_50px_-40px_rgba(161,98,7,0.45)]">
      <p className="font-medium">{queueCount} purchase event(s) waiting to sync.</p>
      {lastError ? <p className="mt-1 text-amber-950/80">{lastError}</p> : null}
    </div>
  );
}
