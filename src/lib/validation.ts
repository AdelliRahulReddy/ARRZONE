import { z } from "zod";

export const enrollmentInputSchema = z.object({
  branchCode: z.string().min(1),
  planId: z.string().min(1),
  fullName: z.string().min(2),
  phone: z.string().min(8),
  email: z.string().email().optional().nullable(),
  consentVersion: z.string().min(1),
  consentAccepted: z.boolean().refine((value) => value, {
    message: "Consent is required.",
  }),
});

export const lookupByQrSchema = z.object({
  qrPayload: z.string().min(1),
});

export const purchaseAddSchema = z.object({
  branchId: z.string().min(1),
  quantity: z.number().int().positive().default(1),
  source: z.enum(["QR_SCAN", "PHONE_LOOKUP"]),
});

export const redeemTokenConsumeSchema = z.object({
  redeemToken: z.string().min(10),
});

export const redeemRecoverySchema = z.object({
  reasonCode: z.string().min(2),
  verificationNote: z.string().min(4),
});

export const reissuePassSchema = z.object({
  reasonCode: z.string().min(2),
});

export const reversalSchema = z.object({
  reasonCode: z.string().min(2),
});

export const mergeMembershipsSchema = z.object({
  survivorMembershipId: z.string().min(1),
  obsoleteMembershipId: z.string().min(1),
  reasonCode: z.string().min(2),
});

export const createPlanSchema = z.object({
  tenantId: z.string().min(1),
  name: z.string().min(2),
  eligibleLabel: z.string().min(2),
  thresholdCount: z.number().int().positive(),
  rewardCreditCount: z.number().int().positive(),
  applicableBranchIds: z.array(z.string()).optional(),
});

export const updatePlanSchema = z.object({
  name: z.string().min(2).optional(),
  eligibleLabel: z.string().min(2).optional(),
  thresholdCount: z.number().int().positive().optional(),
  rewardCreditCount: z.number().int().positive().optional(),
  status: z.enum(["ACTIVE", "INACTIVE"]).optional(),
  applicableBranchIds: z.array(z.string()).optional(),
});

export const createBranchSchema = z.object({
  tenantId: z.string().min(1),
  code: z.string().min(2),
  name: z.string().min(2),
  timezone: z.string().optional(),
  address: z.string().optional(),
});

export const createTenantSchema = z.object({
  name: z.string().min(2),
  slug: z
    .string()
    .min(2)
    .regex(/^[a-z0-9-]+$/)
    .optional(),
});

export const createPlatformAdminUserSchema = z.object({
  fullName: z.string().min(2),
  email: z.string().email(),
  authUserId: z.string().optional(),
});

export const createStaffUserSchema = z.object({
  tenantId: z.string().min(1),
  fullName: z.string().min(2),
  email: z.string().email(),
  role: z.enum(["CASHIER", "MANAGER", "MERCHANT_ADMIN"]),
  primaryBranchId: z.string().min(1).nullable().optional(),
  authUserId: z.string().optional(),
  branchIds: z.array(z.string()).default([]),
});

export const updateStaffUserStatusSchema = z.object({
  status: z.enum(["ACTIVE", "DISABLED"]),
});

export const createBusinessAdminUserSchema = z.object({
  tenantId: z.string().min(1),
  fullName: z.string().min(2),
  email: z.string().email(),
});

export const authSessionSchema = z.object({
  idToken: z.string().min(1),
  redirectTo: z.string().optional(),
});
