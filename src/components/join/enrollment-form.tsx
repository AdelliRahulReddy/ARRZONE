"use client";

import { useEffect, useRef, useTransition } from "react";
import { zodResolver } from "@hookform/resolvers/zod";
import { Controller, useForm, useWatch } from "react-hook-form";
import { z } from "zod";
import { Button } from "@/components/ui/button";
import { Card, CardContent } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import {
  clearEnrollmentDraft,
  saveEnrollmentDraft,
  getEnrollmentDraft,
  saveMemberPass,
} from "@/lib/member-local-state";

const enrollmentSchema = z.object({
  branchCode: z.string().min(1),
  planId: z.string().min(1, "Plan ID is required."),
  fullName: z.string().min(2, "Name is required."),
  phone: z.string().min(8, "Phone number is required."),
  email: z.string().email().optional().or(z.literal("")),
  consentVersion: z.string().min(1),
  consentAccepted: z.boolean().refine((value) => value, {
    message: "You must accept the loyalty program terms to continue.",
  }),
});

type EnrollmentFormValues = z.infer<typeof enrollmentSchema>;

type EnrollmentPlanOption = {
  id: string;
  name: string;
  eligibleLabel: string;
  thresholdCount: number;
  rewardCreditCount: number;
};

export function EnrollmentForm({
  branchCode,
  plans,
}: {
  branchCode: string;
  plans: EnrollmentPlanOption[];
}) {
  const [isPending, startTransition] = useTransition();
  const {
    control,
    register,
    handleSubmit,
    formState: { errors },
    setError,
    reset,
  } = useForm<EnrollmentFormValues>({
    resolver: zodResolver(enrollmentSchema),
    defaultValues: {
      branchCode,
      planId: plans[0]?.id ?? "",
      consentVersion: "v1",
      consentAccepted: true,
    },
  });
  const selectedPlanId = useWatch({
    control,
    name: "planId",
  });
  const watchedDraft = useWatch({
    control,
  });
  const hydratedDraftRef = useRef(false);
  const selectedPlan =
    plans.find((plan) => plan.id === selectedPlanId) ?? plans[0] ?? null;

  useEffect(() => {
    const savedDraft = getEnrollmentDraft(branchCode);
    if (!savedDraft) {
      hydratedDraftRef.current = true;
      return;
    }

    reset({
      branchCode,
      planId:
        plans.some((plan) => plan.id === savedDraft.planId)
          ? savedDraft.planId
          : plans[0]?.id ?? "",
      fullName: savedDraft.fullName,
      phone: savedDraft.phone,
      email: savedDraft.email,
      consentVersion: "v1",
      consentAccepted: true,
    });
    hydratedDraftRef.current = true;
  }, [branchCode, plans, reset]);

  useEffect(() => {
    if (!hydratedDraftRef.current) {
      return;
    }

    saveEnrollmentDraft(branchCode, {
      fullName: watchedDraft.fullName ?? "",
      phone: watchedDraft.phone ?? "",
      email: watchedDraft.email ?? "",
      planId: watchedDraft.planId ?? plans[0]?.id ?? "",
    });
  }, [
    branchCode,
    plans,
    watchedDraft.email,
    watchedDraft.fullName,
    watchedDraft.phone,
    watchedDraft.planId,
  ]);

  const onSubmit = handleSubmit((values) => {
    startTransition(async () => {
      try {
        const response = await fetch("/api/v1/enrollments", {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
          },
          body: JSON.stringify(values),
        });

        const payload = (await response.json()) as {
          ok: boolean;
          data?: { passUrl: string };
          error?: { message: string };
        };

        if (!response.ok || !payload.ok || !payload.data) {
          setError("root", {
            message:
              payload.error?.message ?? "Enrollment failed. Check your setup.",
          });
          return;
        }

        clearEnrollmentDraft(branchCode);
        saveMemberPass(branchCode, payload.data.passUrl);
        window.location.assign(payload.data.passUrl);
      } catch {
        setError("root", {
          message: "Enrollment failed. Check your setup.",
        });
      }
    });
  });

  return (
    <Card className="border-border/70 bg-card/90 shadow-xl shadow-sky-950/5">
      <CardContent className="space-y-6 p-6">
        <div className="grid gap-4 md:grid-cols-2">
          <div className="space-y-2">
            <Label htmlFor="fullName">Full name</Label>
            <Input id="fullName" {...register("fullName")} placeholder="Aarav Kapoor" />
            {errors.fullName ? (
              <p className="text-sm text-destructive">{errors.fullName.message}</p>
            ) : null}
          </div>
          <div className="space-y-2">
            <Label htmlFor="phone">Phone number</Label>
            <Input id="phone" {...register("phone")} placeholder="+91 98765 43210" />
            {errors.phone ? (
              <p className="text-sm text-destructive">{errors.phone.message}</p>
            ) : null}
          </div>
        </div>
        <div className="grid gap-4 md:grid-cols-2">
          <div className="space-y-2">
            <Label htmlFor="email">Email (optional)</Label>
            <Input id="email" {...register("email")} placeholder="hello@example.com" />
          </div>
          <div className="space-y-2">
            <Label htmlFor="planId">Plan</Label>
            <Controller
              name="planId"
              control={control}
              render={({ field }) => (
                <Select
                  value={field.value}
                  onValueChange={field.onChange}
                  disabled={plans.length === 0}
                >
                  <SelectTrigger id="planId" className="h-10 w-full">
                    <SelectValue
                      placeholder={
                        plans.length === 0
                          ? "No active plans for this branch"
                          : "Select a plan"
                      }
                    />
                  </SelectTrigger>
                  <SelectContent>
                    {plans.map((plan) => (
                      <SelectItem key={plan.id} value={plan.id}>
                        {plan.name}
                      </SelectItem>
                    ))}
                  </SelectContent>
                </Select>
              )}
            />
            {errors.planId ? (
              <p className="text-sm text-destructive">{errors.planId.message}</p>
            ) : null}
          </div>
        </div>
        {selectedPlan ? (
          <div className="rounded-2xl border border-border/70 bg-background/70 p-4 text-sm leading-6 text-muted-foreground">
            <p className="font-medium text-foreground">{selectedPlan.name}</p>
            <p>{selectedPlan.eligibleLabel}</p>
            <p className="mt-2">
              Earn {selectedPlan.thresholdCount} purchase{selectedPlan.thresholdCount === 1 ? "" : "s"}
              {" "}to unlock {selectedPlan.rewardCreditCount} reward credit
              {selectedPlan.rewardCreditCount === 1 ? "" : "s"}.
            </p>
          </div>
        ) : (
          <div className="rounded-2xl border border-dashed border-border/80 bg-background/60 p-4 text-sm leading-6 text-muted-foreground">
            No active plans are available for this branch yet.
          </div>
        )}
        <input type="hidden" {...register("branchCode")} />
        <input type="hidden" {...register("consentVersion")} />
        <label className="flex items-start gap-3 rounded-2xl border border-border/70 bg-background/70 p-4 text-sm leading-6 text-muted-foreground">
          <input
            type="checkbox"
            className="mt-1"
            {...register("consentAccepted")}
            aria-label="I agree to the loyalty program terms"
          />
          I agree to the loyalty program terms and privacy notice. The merchant can
          use this contact information to restore my pass and track loyalty events.
        </label>
        {errors.consentAccepted ? (
          <p className="text-sm text-destructive">{errors.consentAccepted.message}</p>
        ) : null}
        {errors.root ? (
          <p className="text-sm text-destructive">{errors.root.message}</p>
        ) : null}
        <Button
          onClick={onSubmit}
          disabled={isPending || plans.length === 0}
          className="w-full rounded-full"
        >
          {isPending ? "Joining..." : "Join and get my pass"}
        </Button>
      </CardContent>
    </Card>
  );
}
