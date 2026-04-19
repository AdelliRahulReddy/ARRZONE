# Firestore Data Model

## Core Collections
- `tenants`: merchant account root documents and platform metadata.
- `branches`: branch records with `tenantId`, QR `code`, status, and display metadata.
- `plans`: mutable plan head documents with `currentVersionId`, threshold config, and branch applicability.
- `plan_versions`: immutable plan snapshots used by memberships.
- `staff_users`: Firebase Auth-linked staff and business-admin records, role, primary branch, cached `branchIds`, and normalized email for first-login binding.
- `platform_admin_users`: Firebase Auth-linked platform-admin records, stored separately from tenant staff records.
- `staff_branch_assignments`: normalized branch assignment documents for auditing and branch-scope verification.
- `customers`: tenant-scoped member identity keyed by normalized phone through a lookup collection.
- `memberships`: active or merged loyalty memberships, canonical merge state, active pass state, and lineage metadata.
- `member_passes`: issued pass versions. Only the membership's `activePassId` is redeem-capable.
- `redeem_tokens`: short-lived single-use redemption tokens keyed by the hashed opaque token value.
- `ledger_events`: immutable source-of-truth event log for purchases, unlocks, redemptions, reversals, reissues, and merges.
- `membership_summaries`: derived projections for fast reads. Rebuilt from ledger for merge and correction flows.
- `idempotency_requests`: scoped write dedupe records keyed by hashed `(tenantId, operation, idempotencyKey)`.
- `security_events`: rate-limit attempts, suspicious activity, and alertable audit records.
- `enrollment_consents`: consent version and hashed-IP capture from public enrollment.
- `membership_merges`: explicit merge audit records.

## Support Collections
- `branch_code_lookups`: guarantees public branch-code resolution for `/join/[branchCode]`.
- `customer_phone_lookups`: maps `(tenantId, normalizedPhone)` to a customer without exposing raw phone numbers in doc ids.
- `active_membership_lookups`: guards one active membership per `(tenantId, customerId, planId)`.

## Invariant Strategy
- One active pass per membership:
  Store `activePassId` and `activePassVersion` on the membership document. All redemption and pass reads verify the pass matches those fields.
- One live redeem token per pass:
  Store `currentRedeemTokenId` on both the active pass and membership. Issuing a new token revokes the previous live token in the same transaction.
- Unique reward unlock per threshold cycle:
  Use deterministic ledger event ids: `reward_unlock:{membershipId}:{cycle}`.
- Non-negative reward balance:
  Enforce in server transactions before summary writes. Merge/correction flows rebuild from ledger and fail if the result would go negative.
- Unique scoped idempotency keys:
  Use deterministic idempotency doc ids derived from `(tenantId, operation, idempotencyKey)`.
- Merge carry-forward:
  Survivor memberships keep `mergedMembershipIds`; rebuild logic reads the survivor lineage and treats it as the canonical reporting identity.
