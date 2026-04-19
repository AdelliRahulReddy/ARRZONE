"use client";

import { useEffect } from "react";
import { saveMemberPass } from "@/lib/member-local-state";

export function PassPersistenceBridge({
  branchCode,
  passUrl,
}: {
  branchCode: string | null;
  passUrl: string;
}) {
  useEffect(() => {
    if (!branchCode) {
      return;
    }

    saveMemberPass(branchCode, passUrl);
  }, [branchCode, passUrl]);

  return null;
}
