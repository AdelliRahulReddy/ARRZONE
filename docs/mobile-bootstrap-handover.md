# Mobile Bootstrap Handover

This note is for the next agent continuing the Flutter/mobile bootstrap work.

## Goal

Fix the temporary Flutter bootstrap flow so the app can resolve the signed-in actor through:

- Firebase ID token
- `GET /api/auth/mobile/me`

The current blocker is not a Flutter parsing issue. The backend is returning a Firestore quota error during actor resolution.

## Current Repo State

Mobile code now exists inside this repo at:

- `mobile/store_ops_app`

Important pieces already implemented:

- Flutter mobile shell with production-style sign-in and automatic bootstrap:
  - `mobile/store_ops_app/lib/main.dart`
- Bearer-token support in server auth:
  - `src/lib/server/firebase-auth.ts`
- Protected API gate allows bearer-authenticated mobile requests:
  - `src/proxy.ts`
- Mobile actor bootstrap route:
  - `src/app/api/auth/mobile/me/route.ts`
- Actor resolution logic:
  - `src/lib/server/auth.ts`

`CODEBASE.md` has already been updated to reflect this status.

## What The Current Flutter Screen Does

The current mobile shell now uses a production-style sign-in/bootstrap flow.

It asks for:

- Firebase email
- Firebase password

Then it:

1. signs in with Firebase Auth
2. fetches the Firebase ID token automatically
3. calls `GET /api/auth/mobile/me`
4. resolves the actor and shows the returned scope

Purpose:

- make login usable on a real device
- remove manual token paste
- remove runtime backend URL prompts
- prove the app can sign in, get a token, and bootstrap itself

Backend selection:

- production default is now hard-wired to `https://arrzone.vercel.app`
- a different backend can still be injected at build time with Dart define:

```bash
flutter build apk --dart-define=ARRZONE_API_BASE_URL=https://your-backend.example.com
```

## Current Fix Direction

The backend no longer has to read Firestore on every auth resolution.

New behavior:

- `src/lib/server/auth.ts` now prefers Firebase custom claims for:
  - staff actor resolution
  - platform-admin actor resolution
- Firestore lookup is now the fallback path, not the primary path
- Claim sync now happens when authz records are created/updated through:
  - `createStaffUser`
  - `updateStaffUserStatus`
  - `updateStaffUser`
  - `createPlatformAdminUser`
  - `updatePlatformAdminUser`
  - `createBusinessAdminUser`
  - `updateBusinessAdminStatus`
- Demo bootstrap seeding now also writes the same access claims onto matched Firebase Auth users

This means mobile bootstrap can survive Firestore quota pressure as long as the caller presents a fresh Firebase token that already contains the synced access claim.

## Why Resolve Exists

Do not remove the resolve step.

Firebase Auth only proves identity:

- who the user is
- whether the token is valid

The app still has to resolve:

- role
- tenant
- branch access
- active/disabled status
- whether this is staff/business-admin or platform-admin

That mapping lives in Firestore and is handled by `src/lib/server/auth.ts`.

## What Needs To Happen Before Retest

1. Sync the demo user claims again:

```bash
npm run firebase:seed:demo
```

2. Sign in again inside the mobile app so Firebase issues a fresh ID token.

Important:

- claim updates now revoke refresh tokens deliberately
- old ID tokens/session cookies should be considered stale after the sync
- mobile bootstrap should be tested only after a fresh in-app sign-in

## Reproduction

1. Start local backend:

```bash
npm run dev
```

2. Generate a fresh Firebase ID token through normal sign-in or Firebase REST auth.

Do not commit tokens, passwords, or temporary credentials into the repo.

3. Call the endpoint directly:

```bash
curl -H "Authorization: Bearer <token>" http://127.0.0.1:3000/api/auth/mobile/me
```

Expected result after the claim sync and a fresh sign-in:

- `/api/auth/mobile/me` should resolve from the token claims
- no Firestore authz lookup should be required for that path
- if the token is stale, missing claims, or belongs to an unsynced user, the backend can still fall back to Firestore and may still hit quota issues

## Remaining Risk

The underlying Firestore quota/billing issue can still affect routes that genuinely need Firestore data.

What changed is narrower and intentional:

- auth resolution is now able to bypass Firestore when a valid synced claim exists
- this specifically reduces breakage for `/api/auth/mobile/me` and other auth-gated routes that only need actor scope

## Files To Read First

1. `src/app/api/auth/mobile/me/route.ts`
2. `src/lib/server/auth.ts`
3. `src/lib/server/firebase-auth.ts`
4. `src/proxy.ts`
5. `mobile/store_ops_app/lib/main.dart`
6. `CODEBASE.md`

## Recommended Next Steps

1. Confirm Firestore quota/billing status in Firebase console.
2. Check whether the project is on a tier with low daily limits.
3. Inspect server-side call volume to authz-heavy routes.
4. Once Firestore reads recover, retest `/api/auth/mobile/me` directly before touching Flutter UI.
5. If bootstrap succeeds, replace the manual token-entry screen with proper Firebase mobile sign-in.
6. Only after that, build the real Flutter flows:
   - scanner
   - member lookup
   - purchase add
   - redeem

## Constraints / User Preferences

- The user does not want unnecessary tests/checks.
- The user asked not to have the agent run the Flutter app again; they will test on their own device.
- The mobile app should be production-style, not ask operators for raw tokens or backend URLs.

## Important Non-Goals Right Now

Do not spend time polishing the temporary Flutter UI before bootstrap works.

The immediate priority is:

- restore backend actor resolution
- prove `/api/auth/mobile/me` works with a valid bearer token
