import type { StaffRole } from "@/lib/firebase/model";

export const ROLE_LABELS = {
  MEMBER: "Member",
  CASHIER: "Counter Staff",
  MANAGER: "Store Manager",
  MERCHANT_ADMIN: "Business Admin",
  PLATFORM_ADMIN: "Platform Admin",
} as const;

export const ACTIVE_STAFF_ROLE_LABELS = [
  ROLE_LABELS.CASHIER,
  ROLE_LABELS.MANAGER,
  ROLE_LABELS.MERCHANT_ADMIN,
] as const;

export function getStaffRoleDisplayName(role: StaffRole) {
  return ROLE_LABELS[role];
}
