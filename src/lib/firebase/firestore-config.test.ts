import { readFileSync } from "node:fs";
import { describe, expect, it } from "vitest";

const rules = readFileSync("firestore.rules", "utf8");
const indexes = JSON.parse(readFileSync("firestore.indexes.json", "utf8")) as {
  indexes: Array<{ collectionGroup: string }>;
};

describe("firebase firestore config", () => {
  it("denies direct client reads and writes by default", () => {
    expect(rules).toContain("allow read, write: if false;");
  });

  it("defines indexes for the main query-heavy collections", () => {
    const collections = indexes.indexes.map((index) => index.collectionGroup);
    expect(collections).toContain("ledger_events");
    expect(collections).toContain("security_events");
    expect(collections).toContain("staff_users");
  });
});
