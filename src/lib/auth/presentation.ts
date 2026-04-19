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
      "Use an approved store account that already matches active counter-staff, store-manager, or business-admin access.",
    panelBadge: "Branch Access",
    panelTitle: "Store Operations Sign-In",
    panelDescription:
      "Email/password and Google are both supported. After sign-in, the app returns you to the operations workspace you requested.",
    requirements: [
      "Verified email",
      "Active staff access",
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
      "Use an approved business-admin account before reviewing plans, branches, staff, and reports.",
    panelBadge: "Tenant Admin Access",
    panelTitle: `${ROLE_LABELS.MERCHANT_ADMIN} Sign-In`,
    panelDescription:
      "Only business admins can open tenant reporting and configuration surfaces.",
    requirements: [
      "Verified email",
      "Active business-admin access",
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
      "Use an approved platform-admin account before reviewing cross-business operations, alerts, and platform controls.",
    panelBadge: "Platform-Wide Access",
    panelTitle: `${ROLE_LABELS.PLATFORM_ADMIN} Sign-In`,
    panelDescription:
      "Only platform admins can open the multi-tenant oversight surface and platform-only controls.",
    requirements: [
      "Verified email",
      "Active platform-admin access",
      "Platform console privileges",
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
        message: `Sign in with an approved account before opening ${definition.surfaceLabel.toLowerCase()}.`,
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
        title: "Platform access disabled",
        message:
          "This platform access record is disabled. Reactivate it or sign in with a different platform account.",
        showSignOut: true,
      };
    case "PLATFORM_ADMIN_NOT_ACTIVE":
      return {
        title: "Platform access not active",
        message:
          "This platform access record is not active yet. Activate it before signing in again.",
        showSignOut: true,
      };
    case "PLATFORM_ADMIN_EMAIL_NOT_VERIFIED":
      return {
        title: "Verified email required",
        message:
          "This email address must be verified before the account can be used for platform access. Verify the email, then try again.",
        showSignOut: true,
      };
    case "PLATFORM_ADMIN_MAPPING_MISSING":
      return {
        title: "No matching platform access",
        message:
          "This account signed in successfully, but it is not on the active platform admin access list. Add it first, then try again.",
        showSignOut: true,
      };
    case "PLATFORM_ADMIN_MAPPING_AMBIGUOUS":
      return {
        title: "Multiple platform access records matched",
        message:
          "This email matched more than one platform access record, so the app could not decide which account to use. Clean up the duplicates and try again.",
        showSignOut: true,
      };
    case "PLATFORM_ADMIN_ALREADY_BOUND":
      return {
        title: "Platform account already linked",
        message:
          "This email is already linked to another sign-in account. Sign in with the originally linked account or update the platform access record.",
        showSignOut: true,
      };
    case "STAFF_DISABLED":
      return {
        title: "Staff access disabled",
        message:
          "This staff access record is disabled. Ask an admin to reactivate it or sign in with a different account.",
        showSignOut: true,
      };
    case "STAFF_NOT_ACTIVE":
      return {
        title: "Staff access not active",
        message:
          "This staff access record is not active yet. Ask an admin to activate it before signing in again.",
        showSignOut: true,
      };
    case "STAFF_EMAIL_NOT_VERIFIED":
      return {
        title: "Verified email required",
        message:
          "This email address must be verified before the account can be used for staff access. Verify the email, then try again.",
        showSignOut: true,
      };
    case "STAFF_MAPPING_MISSING":
      return {
        title: "No matching staff access",
        message:
          "This account signed in successfully, but it is not on the active staff access list. Invite this email first or correct the existing access record.",
        showSignOut: true,
      };
    case "STAFF_MAPPING_AMBIGUOUS":
      return {
        title: "Multiple staff access records matched",
        message:
          "This email matched more than one staff access record, so the app could not decide which account to use. Clean up the duplicates and try again.",
        showSignOut: true,
      };
    case "STAFF_ALREADY_BOUND":
      return {
        title: "Account already linked",
        message:
          "This email is already linked to another sign-in account. Sign in with the originally linked account or update the staff access record.",
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
