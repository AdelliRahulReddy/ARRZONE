import type { StaffRole } from "@/lib/firebase/model";
import {
  BUSINESS_ADMIN_ROUTE,
  LEGACY_BUSINESS_ADMIN_ROUTE,
} from "@/lib/auth/constants";
import {
  ACTIVE_STAFF_ROLE_LABELS,
  ROLE_LABELS,
} from "@/lib/auth/role-labels";
import { AppError } from "@/lib/server/errors";

export type AuthSurface = "staff" | "merchant" | "platform";

type AuthSurfaceDefinition = {
  surfaceLabel: string;
  destination: string;
  eyebrow: string;
  heroTitle: string;
  heroDescription: string;
  panelBadge: string;
  panelTitle: string;
  panelDescription: string;
  requirements: [string, string, string];
  requiredRoleLabel: string;
};

type AuthIssuePresentation = {
  title: string;
  message: string;
  showSignOut: boolean;
};

const surfaceDefinitions: Record<AuthSurface, AuthSurfaceDefinition> = {
  staff: {
    surfaceLabel: "Store Operations",
    destination: "/staff",
    eyebrow: "Store Operations",
    heroTitle: "Sign in to Store Operations",
    heroDescription:
      "Use a verified Firebase account that already matches an active counter-staff, store-manager, or business-admin record. Firestore still decides branch scope and server-side permissions.",
    panelBadge: "Branch-Scoped Access",
    panelTitle: "Store Operations Sign-In",
    panelDescription:
      "Email/password and Google are both supported. After sign-in, the app returns you to the operations workspace you requested.",
    requirements: [
      "Verified Firebase email",
      "Active staff_users record",
      `${ROLE_LABELS.CASHIER}, ${ROLE_LABELS.MANAGER}, or ${ROLE_LABELS.MERCHANT_ADMIN} role`,
    ],
    requiredRoleLabel: ACTIVE_STAFF_ROLE_LABELS.join(", "),
  },
  merchant: {
    surfaceLabel: ROLE_LABELS.MERCHANT_ADMIN,
    destination: BUSINESS_ADMIN_ROUTE,
    eyebrow: ROLE_LABELS.MERCHANT_ADMIN,
    heroTitle: `Sign in to ${ROLE_LABELS.MERCHANT_ADMIN}`,
    heroDescription:
      "Use a verified Firebase account mapped to an active business-admin record before reviewing plans, branches, staff, and reports.",
    panelBadge: "Tenant Admin Access",
    panelTitle: `${ROLE_LABELS.MERCHANT_ADMIN} Sign-In`,
    panelDescription:
      "Only business admins can open tenant reporting and configuration surfaces.",
    requirements: [
      "Verified Firebase email",
      "Active staff_users record",
      `${ROLE_LABELS.MERCHANT_ADMIN} role`,
    ],
    requiredRoleLabel: ROLE_LABELS.MERCHANT_ADMIN,
  },
  platform: {
    surfaceLabel: "Platform Console",
    destination: "/platform",
    eyebrow: ROLE_LABELS.PLATFORM_ADMIN,
    heroTitle: "Sign in to the Platform Console",
    heroDescription:
      "Use a verified Firebase account mapped to an active platform-admin record before reviewing cross-tenant operations, alerts, and platform controls.",
    panelBadge: "Platform-Wide Access",
    panelTitle: `${ROLE_LABELS.PLATFORM_ADMIN} Sign-In`,
    panelDescription:
      "Only platform admins can open the multi-tenant oversight surface and platform-only controls.",
    requirements: [
      "Verified Firebase email",
      "Active platform_admin_users record",
      "Platform admin access managed outside tenant staff",
    ],
    requiredRoleLabel: `Active ${ROLE_LABELS.PLATFORM_ADMIN.toLowerCase()} account`,
  },
};

function normalizeInternalPath(value: string) {
  if (!value.startsWith("/") || value.startsWith("//")) {
    return null;
  }

  try {
    const url = new URL(value, "http://localhost");
    if (
      url.pathname === LEGACY_BUSINESS_ADMIN_ROUTE ||
      url.pathname.startsWith(`${LEGACY_BUSINESS_ADMIN_ROUTE}/`)
    ) {
      url.pathname = `${BUSINESS_ADMIN_ROUTE}${url.pathname.slice(LEGACY_BUSINESS_ADMIN_ROUTE.length)}`;
    }
    return `${url.pathname}${url.search}${url.hash}`;
  } catch {
    return null;
  }
}

export function sanitizeRedirectTarget(value: string | null | undefined) {
  if (!value) {
    return null;
  }

  return normalizeInternalPath(value);
}

export function getAuthSurface(target: string | null | undefined): AuthSurface {
  const normalizedTarget = sanitizeRedirectTarget(target) ?? "/staff";

  if (normalizedTarget === "/platform" || normalizedTarget.startsWith("/platform/")) {
    return "platform";
  }

  if (
    normalizedTarget === BUSINESS_ADMIN_ROUTE ||
    normalizedTarget.startsWith(`${BUSINESS_ADMIN_ROUTE}/`) ||
    normalizedTarget === LEGACY_BUSINESS_ADMIN_ROUTE ||
    normalizedTarget.startsWith(`${LEGACY_BUSINESS_ADMIN_ROUTE}/`)
  ) {
    return "merchant";
  }

  return "staff";
}

export function getAuthSurfaceDefinition(surface: AuthSurface) {
  return surfaceDefinitions[surface];
}

export function getDefaultRedirectForStaffRole(role: StaffRole | null | undefined) {
  if (role === "MERCHANT_ADMIN") {
    return surfaceDefinitions.merchant.destination;
  }

  return surfaceDefinitions.staff.destination;
}

export function getMinimumRoleForSurface(surface: AuthSurface): StaffRole | null {
  switch (surface) {
    case "merchant":
      return "MERCHANT_ADMIN";
    default:
      return null;
  }
}

export function buildSignInHref(target: string) {
  return `/sign-in?redirectTo=${encodeURIComponent(target)}`;
}

export function getAuthIssuePresentation(
  error: unknown,
  surface: AuthSurface,
): AuthIssuePresentation {
  const definition = getAuthSurfaceDefinition(surface);

  if (!(error instanceof AppError)) {
    return {
      title: `${definition.surfaceLabel} is unavailable`,
      message:
        "The app could not verify this session right now. Refresh the page or try signing in again.",
      showSignOut: false,
    };
  }

  switch (error.code) {
    case "UNAUTHORIZED":
      return {
        title: `${definition.surfaceLabel} sign-in required`,
        message: `Sign in with a verified Firebase account before opening ${definition.surfaceLabel.toLowerCase()}.`,
        showSignOut: false,
      };
    case "FORBIDDEN":
      return {
        title: `${definition.surfaceLabel} access denied`,
        message: `This account is signed in, but it does not meet the role requirement for this surface. Required role: ${definition.requiredRoleLabel}.`,
        showSignOut: true,
      };
    case "PLATFORM_ADMIN_DISABLED":
      return {
        title: "Platform admin disabled",
        message:
          "This platform admin record is disabled in Firestore. Reactivate it or sign in with a different platform account.",
        showSignOut: true,
      };
    case "PLATFORM_ADMIN_NOT_ACTIVE":
      return {
        title: "Platform admin not active",
        message:
          "This platform admin record is not active yet. Activate it before signing in again.",
        showSignOut: true,
      };
    case "PLATFORM_ADMIN_EMAIL_NOT_VERIFIED":
      return {
        title: "Verified email required",
        message:
          "Firebase must report a verified email before this account can bind to a platform admin record. Verify the email, then try again.",
        showSignOut: true,
      };
    case "PLATFORM_ADMIN_MAPPING_MISSING":
      return {
        title: "No matching platform admin record",
        message:
          "This Firebase account signed in successfully, but no active platform_admin_users record matched it. Create the platform admin record first, then try again.",
        showSignOut: true,
      };
    case "PLATFORM_ADMIN_MAPPING_AMBIGUOUS":
      return {
        title: "Multiple platform admin records matched",
        message:
          "This email matched more than one platform admin record, so the app could not decide which account to bind. Clean up the duplicate records and try again.",
        showSignOut: true,
      };
    case "PLATFORM_ADMIN_ALREADY_BOUND":
      return {
        title: "Platform account already linked",
        message:
          "This email is already bound to another Firebase Auth user. Sign in with the originally linked account or update the platform admin record.",
        showSignOut: true,
      };
    case "STAFF_DISABLED":
      return {
        title: "Staff account disabled",
        message:
          "This staff record is disabled in Firestore. Ask an admin to reactivate it or sign in with a different account.",
        showSignOut: true,
      };
    case "STAFF_NOT_ACTIVE":
      return {
        title: "Staff account not active",
        message:
          "This staff record is not active yet. Ask an admin to activate it before signing in again.",
        showSignOut: true,
      };
    case "STAFF_EMAIL_NOT_VERIFIED":
      return {
        title: "Verified email required",
        message:
          "Firebase must report a verified email before this account can bind to a staff record. Verify the email, then try again.",
        showSignOut: true,
      };
    case "STAFF_MAPPING_MISSING":
      return {
        title: "No matching staff record",
        message:
          "This Firebase account signed in successfully, but no active staff_users record matched it. Invite this email first or correct the existing staff record.",
        showSignOut: true,
      };
    case "STAFF_MAPPING_AMBIGUOUS":
      return {
        title: "Multiple staff records matched",
        message:
          "This email matched more than one staff record, so the app could not decide which account to bind. Clean up the duplicate records and try again.",
        showSignOut: true,
      };
    case "STAFF_ALREADY_BOUND":
      return {
        title: "Account already linked",
        message:
          "This email is already bound to another Firebase Auth user. Sign in with the originally linked account or update the staff record.",
        showSignOut: true,
      };
    default:
      return {
        title: `${definition.surfaceLabel} is unavailable`,
        message: error.message,
        showSignOut: error.status >= 400 && error.status < 500,
      };
  }
}

export function getOperationIssueMessage(
  error: unknown,
  fallbackMessage: string,
) {
  if (error instanceof AppError) {
    return error.message;
  }

  return fallbackMessage;
}
