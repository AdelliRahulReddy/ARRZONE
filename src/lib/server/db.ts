import "server-only";
import type { Firestore, Transaction } from "firebase-admin/firestore";
import { getFirestoreAdmin } from "@/lib/firebase/admin";

export type AppDatabase = Firestore;
export type AppTransaction = Transaction;

let db: AppDatabase | null = null;

export function getDb() {
  if (!db) {
    db = getFirestoreAdmin();
  }

  return db;
}

export function requireDb() {
  return getDb();
}

export type TenantContext = {
  tenantId: string;
  actorId?: string | null;
  actorRole?: string | null;
};

export async function withTenantTransaction<T>(
  _context: TenantContext,
  run: (tx: AppTransaction, db: AppDatabase) => Promise<T>,
) {
  const database = requireDb();
  return database.runTransaction((tx) => run(tx, database));
}
