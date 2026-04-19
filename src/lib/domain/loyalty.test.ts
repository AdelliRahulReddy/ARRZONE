import { describe, expect, it } from "vitest";
import { getUnlockDelta, rebuildSummary } from "@/lib/domain/loyalty";

describe("loyalty domain math", () => {
  it("unlocks reward credits only when a new threshold cycle is crossed", () => {
    expect(
      getUnlockDelta({
        previousPurchaseCount: 9,
        nextPurchaseCount: 10,
        thresholdCount: 10,
        rewardCreditCount: 2,
      }),
    ).toEqual({
      previousCycle: 0,
      nextCycle: 1,
      cyclesUnlocked: 1,
      rewardCreditsUnlocked: 2,
    });

    expect(
      getUnlockDelta({
        previousPurchaseCount: 10,
        nextPurchaseCount: 10,
        thresholdCount: 10,
        rewardCreditCount: 2,
      }).rewardCreditsUnlocked,
    ).toBe(0);
  });

  it("rebuilds a non-negative membership summary from immutable events", () => {
    const summary = rebuildSummary([
      { eventType: "PURCHASE_ADDED", quantity: 10 },
      { eventType: "REWARD_UNLOCKED", quantity: 2 },
      { eventType: "REWARD_REDEEMED", quantity: 1 },
      { eventType: "PURCHASE_REVERSED", quantity: 1 },
    ]);

    expect(summary).toEqual({
      purchaseCount: 9,
      rewardEarnedCount: 2,
      rewardRedeemedCount: 1,
      rewardBalance: 1,
    });
  });
});
