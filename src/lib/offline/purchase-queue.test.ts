import { describe, expect, it } from "vitest";
import { assertOfflineEligible } from "@/lib/offline/purchase-queue";

describe("offline queue restrictions", () => {
  it("allows purchase-add retries offline", () => {
    expect(() => assertOfflineEligible("purchase-add")).not.toThrow();
  });

  it("blocks redemption and corrective actions from being queued", () => {
    for (const action of [
      "redeem",
      "reverse-purchase",
      "reverse-redeem",
      "reissue-pass",
      "merge",
      "phone-update",
    ]) {
      expect(() => assertOfflineEligible(action)).toThrow(
        `${action} cannot be queued offline.`,
      );
    }
  });
});
