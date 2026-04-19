"use client";

import { useEffect, useState } from "react";
import { Clock3, Ticket } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import {
  clearSavedMemberPass,
  getSavedMemberPass,
  type SavedMemberPass,
} from "@/lib/member-local-state";

export function SavedPassCard({
  branchCode,
  branchName,
}: {
  branchCode: string;
  branchName: string;
}) {
  const [savedPass, setSavedPass] = useState<SavedMemberPass | null>(null);

  useEffect(() => {
    setSavedPass(getSavedMemberPass(branchCode));
  }, [branchCode]);

  if (!savedPass) {
    return null;
  }

  return (
    <Card className="border-emerald-500/20 bg-[linear-gradient(135deg,rgba(17,24,39,0.98),rgba(7,89,133,0.94))] text-white shadow-xl shadow-sky-950/20">
      <CardHeader className="gap-3">
        <div className="inline-flex w-fit items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3 py-1 text-xs uppercase tracking-[0.24em] text-white/75">
          <Ticket className="size-3.5" />
          Saved on this device
        </div>
        <CardTitle className="text-xl text-white">
          Existing member pass found for {branchName}
        </CardTitle>
      </CardHeader>
      <CardContent className="space-y-4">
        <p className="text-sm leading-6 text-white/72">
          This device already has a saved member pass for this branch. Open it
          directly instead of entering the member details again.
        </p>
        <div className="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3 py-1 text-xs text-white/72">
          <Clock3 className="size-3.5" />
          Saved {new Intl.DateTimeFormat("en-IN", { dateStyle: "medium", timeStyle: "short" }).format(new Date(savedPass.savedAt))}
        </div>
        <div className="flex flex-col gap-3 sm:flex-row">
          <Button
            type="button"
            className="rounded-full bg-white text-slate-950 hover:bg-white/90"
            onClick={() => {
              window.location.assign(savedPass.passUrl);
            }}
          >
            Open saved pass
          </Button>
          <Button
            type="button"
            variant="outline"
            className="rounded-full border-white/20 bg-white/5 text-white hover:bg-white/10 hover:text-white"
            onClick={() => {
              clearSavedMemberPass(branchCode);
              setSavedPass(null);
            }}
          >
            Use different details
          </Button>
        </div>
      </CardContent>
    </Card>
  );
}
