# Roles and Surface Access

This document defines the product-facing role names used in the UI and docs.

Important:
- Internal auth enums stay unchanged for now.
- Firestore collections and API guards still use the existing enum values.
- The UI should use the labels in this document instead of exposing raw enum names.

## Product Labels

| Internal code | Product label | Primary surface | Access model |
| --- | --- | --- | --- |
| `CUSTOMER` public user type | Member | `/join/[branchCode]`, `/pass/[token]` | Public signed-link flow, no Firebase Auth |
| `CASHIER` | Counter Staff | `/staff` | Branch-scoped operations |
| `MANAGER` | Store Manager | `/staff` | Branch-scoped operations plus correction controls |
| `MERCHANT_ADMIN` | Business Admin | `/business-admin`, `/staff` | Tenant configuration, reporting, and inherited manager tools |
| `PLATFORM_ADMIN` | Platform Admin | `/platform` | Cross-tenant SaaS oversight |

## Role Explanations

### Member
- Joins from a branch QR flow.
- Does not authenticate with Firebase Auth.
- Opens a signed pass link and can generate a live redeem QR when needed.

### Counter Staff
- Runs the live counter workflow in `/staff`.
- Can resolve members by QR or phone.
- Can add purchases and consume live redeem tokens.
- Cannot use recovery or correction actions.

### Store Manager
- Inherits all Counter Staff abilities.
- Can use higher-risk operational actions in `/staff`:
- phone-based recovery redemption
- pass reissue
- purchase reversal
- redemption reversal
- duplicate membership merge

### Business Admin
- Inherits Store Manager operational access.
- Uses `/business-admin` for business configuration and reporting.
- Manages branches, plans, staff accounts, and tenant-level security review.

Legacy compatibility:
- `/merchant` currently redirects to `/business-admin`.

### Platform Admin
- Uses `/platform`.
- Manages tenants, platform-admin accounts, and global security visibility.
- Is stored in `platform_admin_users`, separate from tenant staff records.

## Naming Rules

- Use `Member` instead of `Customer` in product copy.
- Use `Counter Staff` instead of `Cashier`.
- Use `Store Manager` instead of `Manager`.
- Use `Business Admin` instead of `Merchant Admin`.
- Use `Platform Admin` instead of `SaaS Admin` or `Super Admin`.

## Product Clarifications

- There is no authenticated role literally named `STAFF`.
- `/staff` is a shared operational surface used by Counter Staff, Store Managers, and Business Admins.
- `Business Admin` is a tenant-level business role, not a platform role.
- `Platform Admin` is the only cross-tenant administrative role.
