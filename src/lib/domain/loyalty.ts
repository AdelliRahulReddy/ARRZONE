export type LoyaltyEventType =
  | "MEMBERSHIP_CREATED"
  | "PASS_ISSUED"
  | "PASS_REISSUED"
  | "PURCHASE_ADDED"
  | "PURCHASE_REVERSED"
  | "REWARD_UNLOCKED"
  | "REWARD_REDEEMED"
  | "REWARD_REDEMPTION_REVERSED"
  | "PHONE_UPDATED"
  | "ACCOUNT_MERGED"
  | "PLAN_ASSIGNED"
  | "PLAN_DEACTIVATED_REFERENCE";

export type SummaryEvent = {
  eventType: LoyaltyEventType;
  quantity: number;
};

export type MembershipSummarySnapshot = {
  purchaseCount: number;
  rewardEarnedCount: number;
  rewardRedeemedCount: number;
  rewardBalance: number;
};

export function getUnlockCycle(purchaseCount: number, thresholdCount: number) {
  if (thresholdCount <= 0) {
    return 0;
  }

  return Math.floor(purchaseCount / thresholdCount);
}

export function getUnlockDelta(input: {
  previousPurchaseCount: number;
  nextPurchaseCount: number;
  thresholdCount: number;
  rewardCreditCount: number;
}) {
  const previousCycle = getUnlockCycle(
    input.previousPurchaseCount,
    input.thresholdCount,
  );
  const nextCycle = getUnlockCycle(input.nextPurchaseCount, input.thresholdCount);
  const cyclesUnlocked = Math.max(0, nextCycle - previousCycle);

  return {
    previousCycle,
    nextCycle,
    cyclesUnlocked,
    rewardCreditsUnlocked: cyclesUnlocked * input.rewardCreditCount,
  };
}

export function rebuildSummary(events: SummaryEvent[]): MembershipSummarySnapshot {
  return events.reduce<MembershipSummarySnapshot>(
    (summary, event) => {
      switch (event.eventType) {
        case "PURCHASE_ADDED":
          summary.purchaseCount += event.quantity;
          break;
        case "PURCHASE_REVERSED":
          summary.purchaseCount -= event.quantity;
          break;
        case "REWARD_UNLOCKED":
          summary.rewardEarnedCount += event.quantity;
          summary.rewardBalance += event.quantity;
          break;
        case "REWARD_REDEEMED":
          summary.rewardRedeemedCount += event.quantity;
          summary.rewardBalance -= event.quantity;
          break;
        case "REWARD_REDEMPTION_REVERSED":
          summary.rewardRedeemedCount -= event.quantity;
          summary.rewardBalance += event.quantity;
          break;
        default:
          break;
      }

      if (summary.purchaseCount < 0 || summary.rewardBalance < 0) {
        throw new Error("Summary rebuild encountered an invalid negative state.");
      }

      return summary;
    },
    {
      purchaseCount: 0,
      rewardEarnedCount: 0,
      rewardRedeemedCount: 0,
      rewardBalance: 0,
    },
  );
}
