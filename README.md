# QR Loyalty SaaS

Next.js 16 App Router implementation of the QR loyalty product with:
- Firebase Auth for staff and admin auth
- signed long-lived pass links for customers
- short-lived single-use redeem tokens for redemption
- Firestore as the primary database
- Firebase Admin SDK for all sensitive writes

## Local Setup

1. Install dependencies:

```bash
npm install
```

2. Create `.env.local` from `.env.example`.

3. Configure Firebase one of these ways:
- Emulator:

```env
FIREBASE_PROJECT_ID=arrcloud-637ec
FIRESTORE_EMULATOR_HOST=127.0.0.1:8080
```

Then start Firestore locally:

```bash
npm run firebase:emulators
```

- Real Firebase project:

```env
FIREBASE_PROJECT_ID=arrcloud-637ec
FIREBASE_CLIENT_EMAIL=...
FIREBASE_PRIVATE_KEY="-----BEGIN PRIVATE KEY-----\n...\n-----END PRIVATE KEY-----\n"
```

Or set `GOOGLE_APPLICATION_CREDENTIALS` to a service-account JSON file.

4. Configure Firebase Auth web credentials:

```env
NEXT_PUBLIC_FIREBASE_API_KEY=...
NEXT_PUBLIC_FIREBASE_AUTH_DOMAIN=...
NEXT_PUBLIC_FIREBASE_PROJECT_ID=arrcloud-637ec
NEXT_PUBLIC_FIREBASE_APP_ID=...
```

5. Configure app secrets:

```env
NEXT_PUBLIC_APP_URL=http://localhost:3000
DEFAULT_PHONE_COUNTRY_CODE=+91
PASS_TOKEN_SECRET=...
REDEEM_TOKEN_PEPPER=...
```

6. Run the app:

```bash
npm run dev
```

Optional one-off staff identity migration:

```bash
npm run firebase:migrate:staff-users-auth
```

Bootstrap demo Firestore data:

```bash
npm run firebase:seed:demo
```

This seeds:
- one tenant
- one branch with code `demo-branch`
- one plan and plan version
- one `platform_admin_users` record
- one merchant-admin `staff_users` record when a second Firebase Auth user exists

## Firestore Configuration

- Rules: [firestore.rules](firestore.rules)
- Indexes: [firestore.indexes.json](firestore.indexes.json)
- Data model: [docs/firestore-data-model.md](docs/firestore-data-model.md)
- Migration notes: [docs/firebase-migration.md](docs/firebase-migration.md)
- Full codebase handoff: [CODEBASE.md](CODEBASE.md)

Deploy Firestore config with:

```bash
npm run firebase:deploy:firestore
```

## Product Constraints Preserved

- Four route surfaces remain: customer pass flow, staff app, merchant admin, SaaS admin.
- Customers stay unauthenticated and use signed pass links.
- Static pass QR only opens the pass and supports earning lookup.
- Redemption requires a live short-lived redeem token.
- Only purchase-add can queue offline.
- Redemption and corrective or admin writes remain live-only.
- Ledger events remain the source of truth.
- Summaries stay derived from the ledger and are rebuilt for merge or correction flows.

## Verification

```bash
npm run lint
npm run typecheck
npm test
npm run build
```
