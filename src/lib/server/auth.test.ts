// @vitest-environment node

import { beforeEach, describe, expect, it, vi } from "vitest";

const { getCurrentStaffSessionClaimsMock, requireDbMock } = vi.hoisted(() => ({
  getCurrentStaffSessionClaimsMock: vi.fn(),
  requireDbMock: vi.fn(),
}));

vi.mock("@/lib/server/firebase-auth", () => ({
  getCurrentStaffSessionClaims: getCurrentStaffSessionClaimsMock,
}));

vi.mock("server-only", () => ({}));

vi.mock("@/lib/server/db", () => ({
  requireDb: requireDbMock,
}));

import {
  getCurrentPlatformActor,
  getCurrentStaffActor,
  requirePlatformActor,
  requireStaffActor,
} from "@/lib/server/auth";

type TestStaffUser = {
  id: string;
  tenantId: string;
  authUserId: string | null;
  primaryBranchId: string | null;
  role: string;
  status: string;
  fullName: string;
  email: string;
  emailNormalized: string;
  branchIds: string[];
  createdAt: string;
};

type TestPlatformAdminUser = {
  id: string;
  authUserId: string | null;
  status: string;
  fullName: string;
  email: string;
  emailNormalized: string;
  createdAt: string;
};

type QueryFilter = {
  field: string;
  value: unknown;
};

function makeStaffUser(overrides = {}) {
  return {
    id: "staff-1",
    tenantId: "tenant-1",
    authUserId: "firebase-uid-1",
    primaryBranchId: "branch-1",
    role: "MANAGER",
    status: "ACTIVE",
    fullName: "Taylor Staff",
    email: "staff@example.com",
    emailNormalized: "staff@example.com",
    branchIds: ["branch-1"],
    createdAt: "2026-04-19T00:00:00.000Z",
    ...overrides,
  } satisfies TestStaffUser;
}

function makePlatformAdmin(overrides = {}) {
  return {
    id: "platform-1",
    authUserId: "firebase-platform-1",
    status: "ACTIVE",
    fullName: "Parker Admin",
    email: "platform@example.com",
    emailNormalized: "platform@example.com",
    createdAt: "2026-04-19T00:00:00.000Z",
    ...overrides,
  } satisfies TestPlatformAdminUser;
}

function createAuthDb(input: {
  staffUsers?: TestStaffUser[];
  platformAdmins?: TestPlatformAdminUser[];
}) {
  const staffUsers = new Map(
    (input.staffUsers ?? []).map((staffUser) => [staffUser.id, { ...staffUser }]),
  );
  const platformAdmins = new Map(
    (input.platformAdmins ?? []).map((platformAdmin) => [
      platformAdmin.id,
      { ...platformAdmin },
    ]),
  );

  function rowsForCollection(name: string) {
    if (name === "staff_users") {
      return [...staffUsers.values()];
    }

    if (name === "platform_admin_users") {
      return [...platformAdmins.values()];
    }

    throw new Error(`Unexpected collection: ${name}`);
  }

  function getCollectionMap(name: string) {
    if (name === "staff_users") {
      return staffUsers as Map<string, Record<string, unknown>>;
    }

    if (name === "platform_admin_users") {
      return platformAdmins as Map<string, Record<string, unknown>>;
    }

    throw new Error(`Unexpected collection: ${name}`);
  }

  function matchesFilters(row: Record<string, unknown>, filters: QueryFilter[]) {
    return filters.every(({ field, value }) => row[field] === value);
  }

  function makeSnapshot(rows: Array<Record<string, unknown>>) {
    return {
      empty: rows.length === 0,
      size: rows.length,
      docs: rows.map((row) => ({
        data: () => ({ ...row }),
      })),
    };
  }

  function makeQuery(
    collectionName: string,
    filters: QueryFilter[] = [],
    limitCount = Number.POSITIVE_INFINITY,
  ) {
    return {
      where(field: string, _operator: string, value: unknown) {
        return makeQuery(collectionName, [...filters, { field, value }], limitCount);
      },
      limit(nextLimit: number) {
        return makeQuery(collectionName, filters, nextLimit);
      },
      async get() {
        const rows = rowsForCollection(collectionName)
          .filter((row) => matchesFilters(row, filters))
          .slice(0, limitCount);
        return makeSnapshot(rows);
      },
    };
  }

  return {
    staffUsers,
    platformAdmins,
    collection(name: string) {
      return {
        where(field: string, operator: string, value: unknown) {
          void operator;
          return makeQuery(name, [{ field, value }]);
        },
        doc(id: string) {
          return { id, collectionName: name };
        },
      };
    },
    async runTransaction(
      run: (tx: {
        get: (ref: { id: string; collectionName: string }) => Promise<{
          exists: boolean;
          data: () => Record<string, unknown> | undefined;
        }>;
        set: (ref: { id: string; collectionName: string }, nextValue: Record<string, unknown>) => void;
      }) => Promise<Record<string, unknown>>,
    ) {
      const tx = {
        async get(ref: { id: string; collectionName: string }) {
          const collectionMap = getCollectionMap(ref.collectionName);
          const value = collectionMap.get(ref.id);
          return {
            exists: Boolean(value),
            data: () => (value ? { ...value } : undefined),
          };
        },
        set(
          ref: { id: string; collectionName: string },
          nextValue: Record<string, unknown>,
        ) {
          const collectionMap = getCollectionMap(ref.collectionName);
          collectionMap.set(ref.id, { ...nextValue });
        },
      };

      return run(tx);
    },
  };
}

describe("auth resolution", () => {
  beforeEach(() => {
    vi.clearAllMocks();
  });

  it("returns null when no Firebase session is present", async () => {
    getCurrentStaffSessionClaimsMock.mockResolvedValue(null);

    await expect(getCurrentStaffActor()).resolves.toBeNull();
    await expect(getCurrentPlatformActor()).resolves.toBeNull();
    expect(requireDbMock).not.toHaveBeenCalled();
  });

  it("returns the bound active staff actor for a verified Firebase user", async () => {
    const db = createAuthDb({ staffUsers: [makeStaffUser()] });
    requireDbMock.mockReturnValue(db);
    getCurrentStaffSessionClaimsMock.mockResolvedValue({
      uid: "firebase-uid-1",
      email: "staff@example.com",
      email_verified: true,
    });

    await expect(getCurrentStaffActor()).resolves.toMatchObject({
      userId: "firebase-uid-1",
      staffUserId: "staff-1",
      tenantId: "tenant-1",
      role: "MANAGER",
      authUserId: "firebase-uid-1",
    });
  });

  it("binds the first verified login by normalized email and activates invited staff", async () => {
    const db = createAuthDb({
      staffUsers: [
        makeStaffUser({
          authUserId: null,
          status: "INVITED",
        }),
      ],
    });
    requireDbMock.mockReturnValue(db);
    getCurrentStaffSessionClaimsMock.mockResolvedValue({
      uid: "firebase-uid-2",
      email: "Staff@Example.com",
      email_verified: true,
    });

    const actor = await getCurrentStaffActor();

    expect(actor).toMatchObject({
      userId: "firebase-uid-2",
      authUserId: "firebase-uid-2",
      role: "MANAGER",
    });
    expect(db.staffUsers.get("staff-1")).toMatchObject({
      authUserId: "firebase-uid-2",
      status: "ACTIVE",
      emailNormalized: "staff@example.com",
    });
  });

  it("does not treat a platform admin record as a staff record", async () => {
    const db = createAuthDb({
      platformAdmins: [makePlatformAdmin()],
    });
    requireDbMock.mockReturnValue(db);
    getCurrentStaffSessionClaimsMock.mockResolvedValue({
      uid: "firebase-platform-1",
      email: "platform@example.com",
      email_verified: true,
    });

    await expect(getCurrentStaffActor()).rejects.toMatchObject({
      code: "STAFF_MAPPING_MISSING",
      status: 403,
    });
    await expect(getCurrentPlatformActor()).resolves.toMatchObject({
      role: "PLATFORM_ADMIN",
      platformAdminUserId: "platform-1",
    });
  });

  it("binds the first verified login for a platform admin outside staff records", async () => {
    const db = createAuthDb({
      platformAdmins: [
        makePlatformAdmin({
          authUserId: null,
          status: "INVITED",
        }),
      ],
    });
    requireDbMock.mockReturnValue(db);
    getCurrentStaffSessionClaimsMock.mockResolvedValue({
      uid: "firebase-platform-2",
      email: "Platform@Example.com",
      email_verified: true,
    });

    const actor = await getCurrentPlatformActor();

    expect(actor).toMatchObject({
      userId: "firebase-platform-2",
      authUserId: "firebase-platform-2",
      role: "PLATFORM_ADMIN",
    });
    expect(db.platformAdmins.get("platform-1")).toMatchObject({
      authUserId: "firebase-platform-2",
      status: "ACTIVE",
      emailNormalized: "platform@example.com",
    });
  });

  it("rejects ambiguous staff bindings for the same email", async () => {
    const db = createAuthDb({
      staffUsers: [
        makeStaffUser({ id: "staff-1", authUserId: null }),
        makeStaffUser({ id: "staff-2", authUserId: null }),
      ],
    });
    requireDbMock.mockReturnValue(db);
    getCurrentStaffSessionClaimsMock.mockResolvedValue({
      uid: "firebase-uid-3",
      email: "staff@example.com",
      email_verified: true,
    });

    await expect(getCurrentStaffActor()).rejects.toMatchObject({
      code: "STAFF_MAPPING_AMBIGUOUS",
      status: 409,
    });
  });

  it("rejects disabled staff accounts even when the UID matches", async () => {
    const db = createAuthDb({
      staffUsers: [
        makeStaffUser({
          status: "DISABLED",
        }),
      ],
    });
    requireDbMock.mockReturnValue(db);
    getCurrentStaffSessionClaimsMock.mockResolvedValue({
      uid: "firebase-uid-1",
      email: "staff@example.com",
      email_verified: true,
    });

    await expect(getCurrentStaffActor()).rejects.toMatchObject({
      code: "STAFF_DISABLED",
      status: 403,
    });
  });

  it("rejects first-login binding when the Firebase email is not verified", async () => {
    const db = createAuthDb({
      staffUsers: [
        makeStaffUser({
          authUserId: null,
          status: "INVITED",
        }),
      ],
    });
    requireDbMock.mockReturnValue(db);
    getCurrentStaffSessionClaimsMock.mockResolvedValue({
      uid: "firebase-uid-5",
      email: "staff@example.com",
      email_verified: false,
    });

    await expect(getCurrentStaffActor()).rejects.toMatchObject({
      code: "STAFF_EMAIL_NOT_VERIFIED",
      status: 403,
    });
  });

  it("enforces minimum role checks after resolving the staff actor", async () => {
    const db = createAuthDb({
      staffUsers: [
        makeStaffUser({
          role: "CASHIER",
        }),
      ],
    });
    requireDbMock.mockReturnValue(db);
    getCurrentStaffSessionClaimsMock.mockResolvedValue({
      uid: "firebase-uid-1",
      email: "staff@example.com",
      email_verified: true,
    });

    await expect(requireStaffActor("MERCHANT_ADMIN")).rejects.toMatchObject({
      code: "FORBIDDEN",
      status: 403,
    });
  });

  it("requires an active platform admin record", async () => {
    const db = createAuthDb({
      platformAdmins: [
        makePlatformAdmin({
          status: "DISABLED",
        }),
      ],
    });
    requireDbMock.mockReturnValue(db);
    getCurrentStaffSessionClaimsMock.mockResolvedValue({
      uid: "firebase-platform-1",
      email: "platform@example.com",
      email_verified: true,
    });

    await expect(requirePlatformActor()).rejects.toMatchObject({
      code: "PLATFORM_ADMIN_DISABLED",
      status: 403,
    });
  });
});
