import { describe, expect, it } from "vitest";
import {
  activeMembershipLookupId,
  idempotencyRequestId,
  membershipMergeId,
  phoneLookupId,
  rewardUnlockEventId,
  staffBranchAssignmentId,
} from "@/lib/firebase/model";

describe("firebase document keys", () => {
  it("builds deterministic privacy-safe lookup ids", () => {
    expect(phoneLookupId("tenant-1", "+919999999999")).toBe(
      phoneLookupId("tenant-1", "+919999999999"),
    );
    expect(
      activeMembershipLookupId("tenant-1", "customer-1", "plan-1"),
    ).toBe(activeMembershipLookupId("tenant-1", "customer-1", "plan-1"));
    expect(idempotencyRequestId("tenant-1", "purchase-add", "abc")).toBe(
      idempotencyRequestId("tenant-1", "purchase-add", "abc"),
    );
  });

  it("creates stable ids for invariant-backed documents", () => {
    expect(rewardUnlockEventId("membership-1", 3)).toBe(
      "reward_unlock:membership-1:3",
    );
    expect(staffBranchAssignmentId("staff-1", "branch-1")).toBe(
      "staff-1:branch-1",
    );
    expect(membershipMergeId("survivor-1", "obsolete-1")).toBe(
      "survivor-1:obsolete-1",
    );
  });
});
