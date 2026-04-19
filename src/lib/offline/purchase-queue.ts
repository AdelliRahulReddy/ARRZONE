import { openDB } from "idb";

export type PurchaseQueueItem = {
  id: string;
  membershipId: string;
  branchId: string;
  idempotencyKey: string;
  createdAt: string;
  payload: {
    quantity: number;
    source: "QR_SCAN" | "PHONE_LOOKUP";
  };
};

const DB_NAME = "loyalty-saas-offline";
const STORE_NAME = "purchase_add_queue";

async function getQueueDb() {
  return openDB(DB_NAME, 1, {
    upgrade(database) {
      if (!database.objectStoreNames.contains(STORE_NAME)) {
        database.createObjectStore(STORE_NAME, { keyPath: "id" });
      }
    },
  });
}

export function assertOfflineEligible(action: string) {
  if (action !== "purchase-add") {
    throw new Error(`${action} cannot be queued offline.`);
  }
}

export async function enqueuePurchaseAdd(item: PurchaseQueueItem) {
  assertOfflineEligible("purchase-add");
  const db = await getQueueDb();
  await db.put(STORE_NAME, item);
}

export async function getQueuedPurchaseAdds() {
  const db = await getQueueDb();
  return db.getAll(STORE_NAME);
}

export async function removeQueuedPurchaseAdd(id: string) {
  const db = await getQueueDb();
  await db.delete(STORE_NAME, id);
}
