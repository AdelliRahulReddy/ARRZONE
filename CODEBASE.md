# Codebase Guide

This document is the fastest way for the next agent to get productive in this repo without re-discovering the architecture.

## 1. What This App Is

QR-first loyalty SaaS for offline merchants.

Core behavior:
- Customers do not authenticate.
- Customers join via a branch-specific QR or join link and get a long-lived signed pass link.
- `/join` is a helper landing page that accepts either a full branch join link or a short branch code, but the real enrollment route is `/join/[branchCode]`.
- Static `PASS` QR only opens the pass and supports staff earning lookup.
- Actual redemption requires a short-lived single-use `REDEEM` token.
- Staff/admin auth is handled by Firebase Auth session cookies.
- Firestore is the primary database.
- Firebase Admin SDK is used for all sensitive reads/writes.
- `ledger_events` is the source of truth.
- `membership_summaries` is a derived projection.
- Only `purchase-add` can queue offline.

## 2. Current Real Status

As of the current repo state:
- GitHub remote is `AdelliRahulReddy/ARRZONE`.
- Production is deployed on Vercel at `https://arrzone.vercel.app`.
- Firestore database exists in Firebase project `arrcloud-637ec`.
- Default database `(default)` was created in `asia-south1` (Mumbai).
- Delete protection is enabled on the Firestore database.
- Firestore rules and indexes have already been deployed from this repo.
- Local Firebase Admin credentials are wired through `.env.local` and a repo-local `.secrets/` directory.
- Demo bootstrap data has been seeded once into the live project:
  - tenant `tenant:demo-merchant`
  - branch `branch:demo-merchant:demo-branch`
  - branch code lookup `demo-branch`
  - plan `plan:demo-merchant:default`
  - plan version `plan:demo-merchant:default:v1`
  - one `platform_admin_users` record
  - one business-admin `staff_users` record

Still missing for real app usage:
- Production-specific seed data beyond the local bootstrap defaults.
- Better merchant setup UX; currently some setup is still API/data driven.

## 3. Stack

- Next.js 16 App Router
- React 19
- Firebase Auth for staff/admin auth
- Firestore + Firebase Admin SDK
- Tailwind v4 + shadcn/ui components
- Vitest for unit/integration-style tests
- Playwright smoke test only

## 4. Read These Files First

If a future agent has limited context budget, start here:

1. `src/lib/server/loyalty-service.ts`
   This is the core business/service layer. Nearly every important product rule lives here.
2. `src/lib/firebase/model.ts`
   Firestore collection names, document shapes, and deterministic document-id helpers.
3. `src/proxy.ts`
   Optimistic cookie-presence gate for staff/admin pages and protected API groups.
4. `src/lib/server/auth.ts`
   Verifies the Firebase session cookie and maps it to either `staff_users` or `platform_admin_users`, depending on the requested surface.
5. `docs/roles.md`
   Product-facing role names, route ownership, and the difference between operational and admin surfaces.
6. `scripts/bootstrap-firestore.mjs`
   Seeds a demo tenant, branch, plan/version, platform admin, and business admin into Firestore.
7. `CODEBASE.md`
   This file.

## 5. High-Level Architecture

There is one app, but four route surfaces:

- Member public flow
  - `/join`
  - `/join/[branchCode]`
  - `/pass/[token]`
  - `/pass/[token]/history`
- Staff app
  - `/staff`
- Business admin
  - `/business-admin`
- Platform admin
  - `/platform`

Pattern:
- Page/component layer handles UI.
- App Router route handlers validate input and call service functions.
- `src/lib/server/loyalty-service.ts` performs business logic and Firestore transactions.
- Firestore rules deny direct client access by default.
- Admin SDK bypasses rules, so the server layer is the real trust boundary.

## 6. Auth Model

### Members
- No Firebase Auth
- Access by signed pass link only

### Staff/Admin
- Authenticated with Firebase Auth
- Staff and business-admin access map to Firestore `staff_users`
- Platform-admin access maps to Firestore `platform_admin_users`
- Active staff lookup is done in `src/lib/server/auth.ts`
- Branch authorization comes from `staff_users.branchIds`
- Generic `/sign-in` is surface-aware:
  - `/staff` and `/business-admin` resolve through `staff_users`
  - `/platform` resolves through `platform_admin_users`

### Important auth caveat
- A Firebase user can sign in successfully and still be unauthorized if there is no matching active Firestore authz record for the requested surface.

## 7. Runtime / Boot

Main files:
- `src/app/layout.tsx`
  - Global shell only; auth is handled by Firebase session routes and client Firebase SDK.
- `src/proxy.ts`
  - Protects staff/admin pages and protected API groups with a cookie-presence gate.

Protected route groups:
- `/staff(.*)`
- `/business-admin(.*)`
- `/merchant(.*)` legacy redirect
- `/platform(.*)`
- `/api/v1/memberships(.*)`
- `/api/v1/plans(.*)`
- `/api/v1/branches(.*)`
- `/api/v1/staff-users(.*)`
- `/api/v1/reports(.*)`

Public APIs:
- enrollment
- public pass snapshot
- pass redeem-token generation

## 8. Environment / Secrets

Important env handling is in `src/lib/env.ts`.

Main env vars:
- `NEXT_PUBLIC_APP_URL`
- `DEFAULT_PHONE_COUNTRY_CODE`
- `PASS_TOKEN_SECRET`
- `REDEEM_TOKEN_PEPPER`
- `NEXT_PUBLIC_FIREBASE_API_KEY`
- `NEXT_PUBLIC_FIREBASE_AUTH_DOMAIN`
- `NEXT_PUBLIC_FIREBASE_PROJECT_ID`
- `NEXT_PUBLIC_FIREBASE_APP_ID`
- `FIREBASE_PROJECT_ID`
- `FIREBASE_CLIENT_EMAIL`
- `FIREBASE_PRIVATE_KEY`
- or `GOOGLE_APPLICATION_CREDENTIALS`
- optional `FIRESTORE_EMULATOR_HOST`

Notes:
- `NEXT_PUBLIC_APP_URL` drives public pass URLs and branch join links/QRs. In production it must point at the real public domain.
- For local phone testing, `NEXT_PUBLIC_APP_URL` should be set to the machine's LAN URL rather than `localhost`.
- In dev, `PASS_TOKEN_SECRET` and `REDEEM_TOKEN_PEPPER` have fallback dev defaults.
- In production, both must be explicitly set.
- Local service-account JSON is intentionally kept in `.secrets/`, which is gitignored.

## 9. Firebase / Firestore Integration

Core files:
- `src/lib/firebase/admin.ts`
  - Initializes Firebase Admin using emulator host, explicit service-account env vars, or `GOOGLE_APPLICATION_CREDENTIALS`.
- `src/lib/server/db.ts`
  - Very thin Firestore wrapper. `withTenantTransaction()` now just wraps Firestore transactions.
- `src/lib/firebase/model.ts`
  - Firestore collection names
  - document types
  - deterministic ID helpers
- `scripts/bootstrap-firestore.mjs`
  - Loads `.env.local`
  - Connects with Firebase Admin
  - Seeds demo data into real Firestore or emulator

### Firestore rules

`firestore.rules` intentionally denies all direct reads/writes:
- `allow read, write: if false;`

That is deliberate.
The app is designed around server-only data access.

### Firestore indexes

Defined in `firestore.indexes.json`.
Used mainly for:
- customer phone lookup
- ledger event queries
- membership search
- security event queries
- staff user lookup by Firebase auth user id
- single-field platform admin lookups are handled by Firestore defaults, not composite indexes

## 10. Firestore Collections

Defined in `src/lib/firebase/model.ts`.

Primary collections:
- `tenants`
- `branches`
- `plans`
- `plan_versions`
- `platform_admin_users`
- `staff_users`
- `staff_branch_assignments`
- `customers`
- `memberships`
- `member_passes`
- `redeem_tokens`
- `ledger_events`
- `membership_summaries`
- `idempotency_requests`
- `security_events`
- `enrollment_consents`
- `membership_merges`

Support collections:
- `branch_code_lookups`
- `customer_phone_lookups`
- `active_membership_lookups`

See also:
- `docs/firestore-data-model.md`

## 11. Critical Business Invariants

These are the product rules that must not be weakened:

### Pass and redemption
- Static pass QR is never redeemable.
- It only opens `/pass/[token]` and supports earning lookup.
- `REDEEM` token is server-issued, short-lived, and single-use.
- New redeem token revokes the previous live token for that pass.
- Pass reissue revokes active pass lineage and outstanding redeem token state.

### Offline behavior
- Only `purchase-add` is queueable offline.
- Queue storage lives in IndexedDB via `src/lib/offline/purchase-queue.ts`.
- Redemption, reissue, reversal, merge, and admin/correction flows are live-only.

### Ledger / summary
- `ledger_events` is the source of truth.
- `membership_summaries` is derived state.
- Merge/correction flows rely on rebuild semantics.

### Idempotency
- Every write endpoint expects `Idempotency-Key`.
- Firestore idempotency docs use deterministic ids from:
  - `tenantId`
  - `operation`
  - `idempotencyKey`

### Merge model
- Merge does not rewrite all history.
- Instead, survivor memberships track lineage through `mergedMembershipIds`.
- Summary rebuild reads all lineage memberships.

### Authz
- Branch access is enforced in service code via `assertBranchAccess`.
- Tenant access is enforced in service code via `assertTenantScope`.

## 12. Token Model

### PASS token
File: `src/lib/server/pass-token.ts`

- JWT signed with `jose`
- Contains:
  - `passId`
  - `membershipId`
  - `tenantId`
  - `passVersion`
- Long-lived: expiration is set to `365d`

### REDEEM token
File: `src/lib/server/redeem-token.ts`

- Opaque random token
- Stored only as a hash in Firestore
- TTL: `60s`
- Single-use
- Revocable
- Display preview stores only the last 6 chars

## 13. Core Service Exports

Most of the app’s real behavior is implemented as exported functions in `src/lib/server/loyalty-service.ts`:

- `createEnrollment`
- `getPassSnapshot`
- `getPassHistory`
- `issueRedeemToken`
- `lookupMembershipByPassPayload`
- `searchMembershipsByPhone`
- `getMembershipSnapshotForStaff`
- `getStaffWorkspaceSnapshot`
- `addPurchase`
- `consumeRedeemToken`
- `redeemByRecovery`
- `reissuePass`
- `reversePurchase`
- `reverseRedemption`
- `mergeMemberships`
- `createPlan`
- `updatePlan`
- `createBranch`
- `updateBranch`
- `createStaffUser`
- `updateStaffUserStatus`
- `updateStaffUser`
- `createTenant`
- `updateTenant`
- `createPlatformAdminUser`
- `updatePlatformAdminUser`
- `createBusinessAdminUser`
- `updateBusinessAdminStatus`
- `getPlatformOverviewReport`
- `listPlatformAdminUsers`
- `listTenantDirectory`
- `listBusinessAdminDirectory`
- `getOverviewReport`
- `getStaffActivityReport`
- `getExceptionsReport`
- `getPlatformExceptionsReport`

## 14. API Surface

Route handlers are thin wrappers around validation + service calls.

### Public or customer-facing APIs
- `POST /api/v1/enrollments`
- `GET /api/v1/passes/[token]`
- `POST /api/v1/passes/[token]/redeem-token`

### Staff APIs
- `POST /api/v1/memberships/lookup-by-qr`
- `GET /api/v1/memberships/search`
- `GET /api/v1/memberships/[id]/summary`
- `POST /api/v1/memberships/[id]/purchase-add`
- `POST /api/v1/redemptions/consume`
- `POST /api/v1/memberships/[id]/redeem`
- `POST /api/v1/memberships/[id]/reissue-pass`
- `POST /api/v1/memberships/[id]/reverse-purchase`
- `POST /api/v1/memberships/[id]/reverse-redeem`
- `POST /api/v1/memberships/merge`

### Business admin APIs
- `POST /api/v1/plans`
- `PATCH /api/v1/plans/[id]`
- `POST /api/v1/branches`
- `PATCH /api/v1/branches/[id]`
- `POST /api/v1/staff-users`
- `PATCH /api/v1/staff-users/[id]`
- `GET /api/v1/reports/overview`
- `GET /api/v1/reports/staff-activity`
- `GET /api/v1/reports/exceptions`

### Platform admin
- `POST /api/v1/tenants`
- `PATCH /api/v1/tenants/[id]`
- `POST /api/v1/platform-admin-users`
- `PATCH /api/v1/platform-admin-users/[id]`
- `POST /api/v1/business-admin-users`
- `PATCH /api/v1/business-admin-users/[id]`
- `/platform` also reads cross-tenant overview and security data server-side through `getPlatformOverviewReport()` and `getPlatformExceptionsReport()`

Validation schemas live in `src/lib/validation.ts`.

## 15. UI Surface Notes

### Home
- `src/app/page.tsx`
- Marketing/overview only
- Primary public CTA now points to branch enrollment, not a generic member dashboard

### Enrollment
- `src/app/join/page.tsx`
- Landing page that accepts either a full branch join link or a raw branch code
- Members should ideally arrive from a branch-specific link/QR, not type a code manually
- `src/app/join/[branchCode]/page.tsx`
- Uses `EnrollmentForm`
- Active branch plans are loaded server-side and auto-selected in the form
- Enrollment form state is preserved locally per branch
- If a member already enrolled on the device, the page can offer an `Open saved pass` shortcut before re-entering details
- `src/app/join/[branchCode]/not-found.tsx`
- Dedicated invalid-branch experience for missing/inactive/bad branch links

### Pass
- `src/app/pass/[token]/page.tsx`
- Shows pass summary and static pass QR
- Pass visits persist the branch/pass link locally so returning to the same branch can reopen the saved pass
- `RedeemTokenPanel` generates live redeem token

### Member history
- `src/app/pass/[token]/history/page.tsx`
- Placeholder informational screen, not a full event history UI yet

### Store operations
- `src/components/staff/staff-console.tsx`
- Main operational UI today
- Handles:
  - live camera scanning for pass QR and redeem QR
  - screenshot/photo upload fallback for QR decoding
  - manual QR payload paste
  - phone search fallback for earning
  - purchase add
  - redeem consume
  - manager/business-admin correction controls
  - offline queue status
- Live camera scanning is on-demand and stops after a successful decode
- A phone cannot scan a QR displayed on its own screen; screenshot/photo upload exists for that case
- Main scanner component lives in `src/components/staff/qr-camera-scanner.tsx`
- The workspace was re-laid out for mobile-first use so lookup and selected-member state stay close together on smaller screens

### Business admin
- `src/app/business-admin/page.tsx`
- Reads overview, branches, plans, staff, and tenant security events
- Supports create/edit actions for branches, plans, and staff records, plus inline status management
- Branch directory now includes a deterministic member join-link tool:
  - copyable branch join URL
  - shareable branch QR generated from `/join/[branchCode]`
  - dialog implementation lives in `src/components/merchant/merchant-admin-dashboard.tsx`
- Dashboard uses a shared premium/mobile-first shell with card-based mobile states and inline edit dialogs

### Platform admin
- `src/app/platform/page.tsx`
- Uses `requirePlatformActor()` plus platform overview, tenant directory, platform-admin roster, and security event reads
- Does not rely on `staff_users`
- Supports tenant creation/status updates, platform-admin creation/editing, and business-admin provisioning/status control
- Dashboard uses the same shared premium/mobile-first shell as the business-admin surface

## 16. Offline Queue

Files:
- `src/lib/offline/purchase-queue.ts`
- `src/components/staff/purchase-queue-sync.tsx`

Behavior:
- IndexedDB queue for `purchase-add` only
- Automatically flushes on browser `online`
- Uses original idempotency key during replay

Important:
- There is no queue for redemption or corrections
- That is intentional and should not be generalized casually

## 17. Reporting

Current reporting is intentionally simple:
- overview = counts
- staff activity = basic roster query
- merchant exceptions = tenant-filtered `security_events`
- platform exceptions = cross-tenant `security_events`

This is enough for scaffolding, but not final analytics.

## 18. Security / Abuse Controls

Files:
- `src/lib/server/security.ts`
- constants in `src/lib/constants.ts`

Implemented:
- phone search rate limit
- pass lookup rate limit
- QR lookup rate limit
- redeem token generation rate limit
- redeem consume rate limit
- suspicious activity logging in `security_events`

Not implemented with external infrastructure:
- no Redis / edge rate limiter
- rate limiting is Firestore-backed and server-side only

## 19. Tests

Current tests:
- `src/lib/domain/loyalty.test.ts`
- `src/lib/qr.test.ts`
- `src/lib/offline/purchase-queue.test.ts`
- `src/lib/server/redeem-token.test.ts`
- `src/lib/server/firebase-auth.test.ts`
- `src/lib/server/auth.test.ts`
- `src/lib/firebase/model.test.ts`
- `src/lib/firebase/firestore-config.test.ts`
- `src/lib/auth/presentation.test.ts`
- `src/app/api/auth/session/route.test.ts`
- `src/components/auth/staff-sign-in-panel.test.tsx`
- `src/test/e2e/smoke.spec.ts`

Reality:
- unit coverage is okay for helpers and config
- there is no full seeded Firestore integration test suite yet
- Playwright only contains a smoke check for the landing page
- There is no automated browser coverage for camera permissions, live QR scanning, or screenshot-upload decoding yet

## 20. Known Gaps / Honest Status

These are important and should be assumed true until changed:

- No automatic merchant bootstrap flow exists yet
- Merchant analytics still need date-range and comparative reporting
- Platform console still lacks deeper provisioning/billing automation beyond tenant/admin record management
- No Firestore-backed integration test harness exists yet
- No meaningful browser automation exists yet for the QR scanner flows across real mobile permission states
- Bootstrap data can be created with `npm run firebase:seed:demo`, but production onboarding is still manual

## 21. Recommended Next Work

Best next steps, in order:

1. Add browser-level verification for live camera scan, screenshot-upload decode, and permission-denied fallback states.
2. Add Firestore integration tests around `loyalty-service.ts`.
3. Add date-range, branch, and plan filters to business reporting.
4. Add delete/archive confirmation flows and deeper audit views across the admin consoles.

## 22. If You Need to Change Business Rules

Do not start in the page layer.
Start in this order:

1. `src/lib/server/loyalty-service.ts`
2. `src/lib/firebase/model.ts`
3. `src/lib/validation.ts`
4. API route handlers
5. UI components

Reason:
- the service layer is the real source of business behavior
- route handlers are thin
- UI should reflect service behavior, not invent it

## 23. Commands

Local development:

```bash
npm install
npm run dev
```

Verification:

```bash
npm run lint
npm run typecheck
npm test
npm run build
```

Firestore emulator:

```bash
npm run firebase:emulators
```

Seed demo Firestore data:

```bash
npm run firebase:seed:demo
```

Deploy Firestore rules/indexes:

```bash
npm run firebase:deploy:firestore
```

## 24. Repo Docs

Existing docs:
- `README.md`
- `docs/firestore-data-model.md`
- `docs/firebase-migration.md`

Use them for:
- setup
- data model
- migration rationale

Use this file for:
- fast agent orientation
- current architecture
- practical continuation guidance
