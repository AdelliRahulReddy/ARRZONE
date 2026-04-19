# Firebase Migration Notes

## Removed
- Drizzle schema definitions
- SQL migration files and migration metadata
- Neon/Postgres connection helpers
- Postgres-specific RLS policy setup
- SQL trigger/invariant enforcement code paths

## Replaced With
- Firebase Admin SDK bootstrap in `src/lib/firebase/admin.ts`
- Firestore document model and deterministic lookup ids in `src/lib/firebase/model.ts`
- Firestore rules in `firestore.rules`
- Firestore composite indexes in `firestore.indexes.json`
- Firestore transaction-backed service logic in `src/lib/server/loyalty-service.ts`

## Tradeoffs vs Postgres
- Firestore does not provide SQL constraints, joins, or database triggers. The app now enforces invariants with deterministic document ids, lookup collections, and server-only transactions.
- Merge flows no longer rewrite every ledger row to the survivor membership. Instead, survivor memberships track merged lineage ids and rebuild summaries from that canonical lineage.
- Reporting remains correct, but some aggregate queries are more application-driven than database-native.
- Firestore rules protect direct client SDK access, while the Firebase Admin SDK bypasses rules by design; the application must keep privileged access strictly server-only.
