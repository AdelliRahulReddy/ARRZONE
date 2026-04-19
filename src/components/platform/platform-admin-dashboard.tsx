"use client";

import { startTransition, useState, type FormEvent } from "react";
import { format } from "date-fns";
import {
  AlertTriangle,
  Building2,
  Layers3,
  Plus,
  ShieldAlert,
  ShieldCheck,
  Store,
  Users,
} from "lucide-react";
import { useRouter } from "next/navigation";
import {
  AdminMetricCard,
  DashboardEmptyState,
  DashboardStatusBadge,
} from "@/components/admin/dashboard-primitives";
import { Alert, AlertDescription, AlertTitle } from "@/components/ui/alert";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from "@/components/ui/dialog";
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
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from "@/components/ui/table";
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs";
import type { PlatformAdminUserDoc, SecurityEventDoc } from "@/lib/firebase/model";

type PlatformAdminDashboardProps = {
  overview: {
    totalTenants: number;
    activeTenants: number;
    totalBranches: number;
    totalPlans: number;
    totalMerchantStaff: number;
    totalBusinessAdmins: number;
    totalPlatformAdmins: number;
    totalMemberships: number;
    totalSecurityEvents: number;
  };
  tenants: Array<{
    id: string;
    slug: string;
    name: string;
    status: string;
    createdAt: string;
    branchCount: number;
    planCount: number;
    staffCount: number;
    membershipCount: number;
    merchantAdmins: string[];
  }>;
  platformAdmins: PlatformAdminUserDoc[];
  businessAdmins: Array<{
    id: string;
    tenantId: string;
    tenantName: string;
    tenantSlug: string;
    fullName: string;
    email: string;
    status: string;
    primaryBranchName: string | null;
    branchCount: number;
    authLinked: boolean;
    createdAt: string;
  }>;
  exceptions: SecurityEventDoc[];
};

type RequestPayload<T> = {
  ok: boolean;
  data?: T;
  error?: {
    message?: string;
  };
};

function formatDate(value: string) {
  return format(new Date(value), "dd MMM yyyy");
}

async function requestJson<T>(input: RequestInfo, init: RequestInit) {
  const response = await fetch(input, {
    ...init,
    headers: {
      "Content-Type": "application/json",
      ...(init.headers ?? {}),
    },
  });

  const payload = (await response.json().catch(() => null)) as RequestPayload<T> | null;
  if (!response.ok || !payload?.ok) {
    throw new Error(payload?.error?.message || "Request failed.");
  }

  return payload.data as T;
}

export function PlatformAdminDashboard({
  overview,
  tenants,
  platformAdmins,
  businessAdmins,
  exceptions,
}: PlatformAdminDashboardProps) {
  const router = useRouter();
  const [statusPendingId, setStatusPendingId] = useState<string | null>(null);
  const [statusError, setStatusError] = useState("");

  async function handleBusinessAdminStatusChange(
    businessAdminId: string,
    nextStatus: "ACTIVE" | "DISABLED",
  ) {
    setStatusPendingId(businessAdminId);
    setStatusError("");

    try {
      await requestJson(`/api/v1/business-admin-users/${businessAdminId}`, {
        method: "PATCH",
        body: JSON.stringify({ status: nextStatus }),
      });
      startTransition(() => {
        router.refresh();
      });
    } catch (error) {
      setStatusError(
        error instanceof Error ? error.message : "Failed to update business admin status.",
      );
    } finally {
      setStatusPendingId(null);
    }
  }

  return (
    <div className="space-y-6">
      <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
        <AdminMetricCard
          title="Tenants"
          value={overview.totalTenants}
          description="Businesses currently provisioned on the platform."
          icon={Building2}
        />
        <AdminMetricCard
          title="Branches"
          value={overview.totalBranches}
          description="Branch footprints across all tenants."
          icon={Store}
        />
        <AdminMetricCard
          title="Business Admins"
          value={overview.totalBusinessAdmins}
          description="Tenant-level business admins currently managed from the platform."
          icon={Users}
        />
        <AdminMetricCard
          title="Platform Admins"
          value={overview.totalPlatformAdmins}
          description="Platform admins managed outside tenant staff records."
          icon={ShieldCheck}
        />
        <AdminMetricCard
          title="Security Events"
          value={overview.totalSecurityEvents}
          description="Global operational alerts and suspicious activity records."
          icon={ShieldAlert}
        />
      </div>

      <Tabs defaultValue="overview" className="space-y-4">
        <TabsList variant="line" className="w-full justify-start overflow-x-auto">
          <TabsTrigger value="overview">Overview</TabsTrigger>
          <TabsTrigger value="tenants">Tenants</TabsTrigger>
          <TabsTrigger value="business-admins">Business Admins</TabsTrigger>
          <TabsTrigger value="platform-admins">Platform Admins</TabsTrigger>
          <TabsTrigger value="security">Security</TabsTrigger>
        </TabsList>

        <TabsContent value="overview" className="space-y-4">
          <div className="grid gap-4 lg:grid-cols-[1.15fr_0.85fr]">
            <div className="rounded-3xl border border-border/70 bg-card/90 p-6 shadow-sm">
              <p className="text-sm uppercase tracking-[0.24em] text-muted-foreground">
                Global health
              </p>
              <h2 className="mt-2 text-2xl font-semibold tracking-tight">
                Cross-tenant operations at a glance
              </h2>
              <div className="mt-6 grid gap-4 md:grid-cols-2">
                <div className="rounded-2xl border border-border/70 bg-background/80 p-4">
                  <p className="text-sm font-medium">Active tenants</p>
                  <p className="mt-2 text-4xl font-semibold tracking-tight">
                    {overview.activeTenants}
                  </p>
                </div>
                <div className="rounded-2xl border border-border/70 bg-background/80 p-4">
                  <p className="text-sm font-medium">Membership footprint</p>
                  <p className="mt-2 text-4xl font-semibold tracking-tight">
                    {overview.totalMemberships}
                  </p>
                </div>
                <div className="rounded-2xl border border-border/70 bg-background/80 p-4">
                  <p className="text-sm font-medium">Business admin coverage</p>
                  <p className="mt-2 text-4xl font-semibold tracking-tight">
                    {overview.totalBusinessAdmins}
                  </p>
                </div>
                <div className="rounded-2xl border border-border/70 bg-background/80 p-4">
                  <p className="text-sm font-medium">Merchant staff footprint</p>
                  <p className="mt-2 text-4xl font-semibold tracking-tight">
                    {overview.totalMerchantStaff}
                  </p>
                </div>
              </div>
            </div>

            <div className="rounded-3xl border border-border/70 bg-card/90 p-6 shadow-sm">
              <p className="text-sm uppercase tracking-[0.24em] text-muted-foreground">
                Quick Actions
              </p>
              <div className="mt-4 flex flex-col gap-3">
                <CreateTenantDialog />
                <InviteBusinessAdminDialog tenants={tenants} />
                <InvitePlatformAdminDialog />
              </div>
            </div>
          </div>
        </TabsContent>

        <TabsContent value="tenants" className="space-y-4">
          <div className="rounded-3xl border border-border/70 bg-card/90 p-6 shadow-sm">
            <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
              <div>
                <p className="text-sm uppercase tracking-[0.24em] text-muted-foreground">
                  Tenant Directory
                </p>
                <h2 className="mt-2 text-2xl font-semibold tracking-tight">
                  Merchant footprints and coverage
                </h2>
              </div>
              <CreateTenantDialog />
            </div>
            <div className="mt-6">
              {tenants.length === 0 ? (
                <DashboardEmptyState
                  title="No tenants provisioned"
                  description="Create the first tenant to start onboarding branches, plans, and merchant staff."
                />
              ) : (
                <Table>
                  <TableHeader>
                    <TableRow>
                      <TableHead>Tenant</TableHead>
                      <TableHead>Status</TableHead>
                      <TableHead>Branches</TableHead>
                      <TableHead>Plans</TableHead>
                      <TableHead>Staff</TableHead>
                      <TableHead>Memberships</TableHead>
                      <TableHead>Business Admins</TableHead>
                    </TableRow>
                  </TableHeader>
                  <TableBody>
                    {tenants.map((tenant) => (
                      <TableRow key={tenant.id}>
                        <TableCell>
                          <div>
                            <p className="font-medium">{tenant.name}</p>
                            <p className="text-xs text-muted-foreground">
                              {tenant.slug} · {tenant.id}
                            </p>
                          </div>
                        </TableCell>
                        <TableCell>
                          <DashboardStatusBadge value={tenant.status} />
                        </TableCell>
                        <TableCell>{tenant.branchCount}</TableCell>
                        <TableCell>{tenant.planCount}</TableCell>
                        <TableCell>{tenant.staffCount}</TableCell>
                        <TableCell>{tenant.membershipCount}</TableCell>
                        <TableCell>
                          {tenant.merchantAdmins.length === 0
                            ? "Unassigned"
                            : tenant.merchantAdmins.join(", ")}
                        </TableCell>
                      </TableRow>
                    ))}
                  </TableBody>
                </Table>
              )}
            </div>
          </div>
        </TabsContent>

        <TabsContent value="business-admins" className="space-y-4">
          <div className="rounded-3xl border border-border/70 bg-card/90 p-6 shadow-sm">
            <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
              <div>
                <p className="text-sm uppercase tracking-[0.24em] text-muted-foreground">
                  Business Admins
                </p>
                <h2 className="mt-2 text-2xl font-semibold tracking-tight">
                  Tenant admin roster and access state
                </h2>
              </div>
              <InviteBusinessAdminDialog tenants={tenants} />
            </div>
            <div className="mt-6 space-y-4">
              {statusError ? (
                <Alert variant="destructive">
                  <AlertTriangle />
                  <AlertTitle>Business admin update failed</AlertTitle>
                  <AlertDescription>{statusError}</AlertDescription>
                </Alert>
              ) : null}
              {businessAdmins.length === 0 ? (
                <DashboardEmptyState
                  title="No business admins assigned"
                  description="Create a tenant first, then invite a business admin to own its branch, plan, and staff setup."
                />
              ) : (
                <Table>
                  <TableHeader>
                    <TableRow>
                      <TableHead>Name</TableHead>
                      <TableHead>Business</TableHead>
                      <TableHead>Status</TableHead>
                      <TableHead>Branch Scope</TableHead>
                      <TableHead>Sign-In Status</TableHead>
                      <TableHead>Created</TableHead>
                      <TableHead className="text-right">Action</TableHead>
                    </TableRow>
                  </TableHeader>
                  <TableBody>
                    {businessAdmins.map((businessAdmin) => {
                      const shouldActivate = businessAdmin.status !== "ACTIVE";
                      const nextStatus = shouldActivate ? "ACTIVE" : "DISABLED";
                      const actionLabel = shouldActivate ? "Activate" : "Disable";

                      return (
                        <TableRow key={businessAdmin.id}>
                          <TableCell>
                            <div>
                              <p className="font-medium">{businessAdmin.fullName}</p>
                              <p className="text-xs text-muted-foreground">
                                {businessAdmin.email}
                              </p>
                            </div>
                          </TableCell>
                          <TableCell>
                            <div>
                              <p className="font-medium">{businessAdmin.tenantName}</p>
                              <p className="text-xs text-muted-foreground">
                                {businessAdmin.tenantSlug}
                              </p>
                            </div>
                          </TableCell>
                          <TableCell>
                            <DashboardStatusBadge value={businessAdmin.status} />
                          </TableCell>
                          <TableCell>
                            {businessAdmin.primaryBranchName
                              ? businessAdmin.branchCount > 1
                                ? `${businessAdmin.primaryBranchName} + ${businessAdmin.branchCount - 1} more`
                                : businessAdmin.primaryBranchName
                              : businessAdmin.branchCount > 0
                                ? `${businessAdmin.branchCount} branches`
                                : "No branch assignments yet"}
                          </TableCell>
                          <TableCell>
                            {businessAdmin.authLinked ? (
                              <Badge variant="outline" className="rounded-full">
                                Linked
                              </Badge>
                            ) : (
                              <Badge variant="secondary" className="rounded-full">
                                Awaiting first login
                              </Badge>
                            )}
                          </TableCell>
                          <TableCell>{formatDate(businessAdmin.createdAt)}</TableCell>
                          <TableCell className="text-right">
                            <Button
                              type="button"
                              variant={nextStatus === "DISABLED" ? "outline" : "default"}
                              className="rounded-full"
                              disabled={statusPendingId === businessAdmin.id}
                              onClick={() =>
                                void handleBusinessAdminStatusChange(
                                  businessAdmin.id,
                                  nextStatus,
                                )
                              }
                            >
                              {statusPendingId === businessAdmin.id ? "Saving..." : actionLabel}
                            </Button>
                          </TableCell>
                        </TableRow>
                      );
                    })}
                  </TableBody>
                </Table>
              )}
            </div>
          </div>
        </TabsContent>

        <TabsContent value="platform-admins" className="space-y-4">
          <div className="rounded-3xl border border-border/70 bg-card/90 p-6 shadow-sm">
            <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
              <div>
                <p className="text-sm uppercase tracking-[0.24em] text-muted-foreground">
                  Platform Admins
                </p>
                <h2 className="mt-2 text-2xl font-semibold tracking-tight">
                  Platform admin roster
                </h2>
              </div>
              <InvitePlatformAdminDialog />
            </div>
            <div className="mt-6">
              {platformAdmins.length === 0 ? (
                <DashboardEmptyState
                  title="No platform admins invited"
                  description="Create a platform admin record to grant access to the platform console."
                />
              ) : (
                <Table>
                  <TableHeader>
                    <TableRow>
                      <TableHead>Name</TableHead>
                      <TableHead>Email</TableHead>
                      <TableHead>Status</TableHead>
                      <TableHead>Sign-In Status</TableHead>
                      <TableHead>Created</TableHead>
                    </TableRow>
                  </TableHeader>
                  <TableBody>
                    {platformAdmins.map((platformAdmin) => (
                      <TableRow key={platformAdmin.id}>
                        <TableCell>
                          <div>
                            <p className="font-medium">{platformAdmin.fullName}</p>
                            <p className="text-xs text-muted-foreground">{platformAdmin.id}</p>
                          </div>
                        </TableCell>
                        <TableCell>{platformAdmin.email}</TableCell>
                        <TableCell>
                          <DashboardStatusBadge value={platformAdmin.status} />
                        </TableCell>
                        <TableCell>
                          {platformAdmin.authUserId ? (
                            <Badge variant="outline" className="rounded-full">
                              Linked
                            </Badge>
                          ) : (
                            <Badge variant="secondary" className="rounded-full">
                              Awaiting first login
                            </Badge>
                          )}
                        </TableCell>
                        <TableCell>{formatDate(platformAdmin.createdAt)}</TableCell>
                      </TableRow>
                    ))}
                  </TableBody>
                </Table>
              )}
            </div>
          </div>
        </TabsContent>

        <TabsContent value="security" className="space-y-4">
          <div className="rounded-3xl border border-border/70 bg-card/90 p-6 shadow-sm">
            <div className="flex items-start gap-3 rounded-2xl border border-border/70 bg-background/70 p-4">
              <Layers3 className="mt-0.5 size-5 text-primary" />
              <div>
                <p className="font-medium">Global security event stream</p>
                <p className="mt-2 text-sm leading-6 text-muted-foreground">
                  This global event stream highlights rate limits, suspicious activity,
                  and other platform-wide operational signals across every tenant.
                </p>
              </div>
            </div>
            <div className="mt-6">
              {exceptions.length === 0 ? (
                <DashboardEmptyState
                  title="No global security events recorded"
                  description="Security events appear here once rate limits or suspicious activity fire in any tenant."
                />
              ) : (
                <Table>
                  <TableHeader>
                    <TableRow>
                      <TableHead>Event</TableHead>
                      <TableHead>Tenant</TableHead>
                      <TableHead>Scope</TableHead>
                      <TableHead>Created</TableHead>
                    </TableRow>
                  </TableHeader>
                  <TableBody>
                    {exceptions.map((event) => (
                      <TableRow key={event.id}>
                        <TableCell>{event.eventType}</TableCell>
                        <TableCell>{event.tenantId ?? "Platform"}</TableCell>
                        <TableCell>{event.scopeKey}</TableCell>
                        <TableCell>{formatDate(event.createdAt)}</TableCell>
                      </TableRow>
                    ))}
                  </TableBody>
                </Table>
              )}
            </div>
          </div>
        </TabsContent>
      </Tabs>
    </div>
  );
}

function CreateTenantDialog() {
  const router = useRouter();
  const [open, setOpen] = useState(false);
  const [pending, setPending] = useState(false);
  const [errorMessage, setErrorMessage] = useState("");

  async function handleSubmit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();
    setPending(true);
    setErrorMessage("");

    const form = event.currentTarget;
    const formData = new FormData(form);

    try {
      await requestJson("/api/v1/tenants", {
        method: "POST",
        body: JSON.stringify({
          name: String(formData.get("name") ?? ""),
          slug: String(formData.get("slug") ?? "") || undefined,
        }),
      });
      form.reset();
      setOpen(false);
      startTransition(() => {
        router.refresh();
      });
    } catch (error) {
      setErrorMessage(error instanceof Error ? error.message : "Failed to create tenant.");
    } finally {
      setPending(false);
    }
  }

  return (
    <Dialog open={open} onOpenChange={setOpen}>
      <DialogTrigger asChild>
        <Button className="rounded-full">
          <Plus />
          Create Tenant
        </Button>
      </DialogTrigger>
      <DialogContent className="sm:max-w-lg">
        <DialogHeader>
          <DialogTitle>Create Tenant</DialogTitle>
          <DialogDescription>
            Provision a merchant root document. Branches, plans, and business admins can be added after creation.
          </DialogDescription>
        </DialogHeader>
        <form className="space-y-4" onSubmit={handleSubmit}>
          <div className="grid gap-4 sm:grid-cols-2">
            <div className="space-y-2">
              <Label htmlFor="tenant-name">Tenant name</Label>
              <Input id="tenant-name" name="name" placeholder="Acme Coffee" required />
            </div>
            <div className="space-y-2">
              <Label htmlFor="tenant-slug">Slug</Label>
              <Input id="tenant-slug" name="slug" placeholder="acme-coffee" />
            </div>
          </div>
          {errorMessage ? (
            <Alert variant="destructive">
              <AlertTriangle />
              <AlertTitle>Tenant creation failed</AlertTitle>
              <AlertDescription>{errorMessage}</AlertDescription>
            </Alert>
          ) : null}
          <DialogFooter>
            <Button type="submit" className="rounded-full" disabled={pending}>
              {pending ? "Creating..." : "Create Tenant"}
            </Button>
          </DialogFooter>
        </form>
      </DialogContent>
    </Dialog>
  );
}

function InviteBusinessAdminDialog({
  tenants,
}: {
  tenants: PlatformAdminDashboardProps["tenants"];
}) {
  const router = useRouter();
  const [open, setOpen] = useState(false);
  const [pending, setPending] = useState(false);
  const [errorMessage, setErrorMessage] = useState("");
  const [tenantId, setTenantId] = useState(tenants[0]?.id ?? "");

  async function handleSubmit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();
    setPending(true);
    setErrorMessage("");

    const form = event.currentTarget;
    const formData = new FormData(form);

    try {
      await requestJson("/api/v1/business-admin-users", {
        method: "POST",
        body: JSON.stringify({
          tenantId,
          fullName: String(formData.get("fullName") ?? ""),
          email: String(formData.get("email") ?? ""),
        }),
      });
      form.reset();
      setTenantId(tenants[0]?.id ?? "");
      setOpen(false);
      startTransition(() => {
        router.refresh();
      });
    } catch (error) {
      setErrorMessage(
        error instanceof Error ? error.message : "Failed to invite business admin.",
      );
    } finally {
      setPending(false);
    }
  }

  return (
    <Dialog open={open} onOpenChange={setOpen}>
      <DialogTrigger asChild>
        <Button className="rounded-full" disabled={tenants.length === 0}>
          <Plus />
          Invite Business Admin
        </Button>
      </DialogTrigger>
      <DialogContent className="sm:max-w-lg">
        <DialogHeader>
          <DialogTitle>Invite Business Admin</DialogTitle>
          <DialogDescription>
            Create a tenant-level business admin record and assign it to the selected business.
          </DialogDescription>
        </DialogHeader>
        <form className="space-y-4" onSubmit={handleSubmit}>
          <div className="space-y-2">
            <Label>Business</Label>
            <Select value={tenantId} onValueChange={setTenantId}>
              <SelectTrigger className="w-full">
                <SelectValue placeholder="Select a business" />
              </SelectTrigger>
              <SelectContent>
                {tenants.map((tenant) => (
                  <SelectItem key={tenant.id} value={tenant.id}>
                    {tenant.name}
                  </SelectItem>
                ))}
              </SelectContent>
            </Select>
          </div>
          <div className="grid gap-4 sm:grid-cols-2">
            <div className="space-y-2">
              <Label htmlFor="business-admin-name">Full name</Label>
              <Input
                id="business-admin-name"
                name="fullName"
                placeholder="Jordan Business"
                required
              />
            </div>
            <div className="space-y-2">
              <Label htmlFor="business-admin-email">Email</Label>
              <Input id="business-admin-email" name="email" type="email" required />
            </div>
          </div>
          {tenants.length === 0 ? (
            <Alert variant="destructive">
              <AlertTriangle />
              <AlertTitle>Create a tenant first</AlertTitle>
              <AlertDescription>
                Business admins can only be invited after a tenant exists.
              </AlertDescription>
            </Alert>
          ) : null}
          {errorMessage ? (
            <Alert variant="destructive">
              <AlertTriangle />
              <AlertTitle>Business admin invite failed</AlertTitle>
              <AlertDescription>{errorMessage}</AlertDescription>
            </Alert>
          ) : null}
          <DialogFooter>
            <Button
              type="submit"
              className="rounded-full"
              disabled={pending || tenants.length === 0 || !tenantId}
            >
              {pending ? "Creating..." : "Create Business Admin"}
            </Button>
          </DialogFooter>
        </form>
      </DialogContent>
    </Dialog>
  );
}

function InvitePlatformAdminDialog() {
  const router = useRouter();
  const [open, setOpen] = useState(false);
  const [pending, setPending] = useState(false);
  const [errorMessage, setErrorMessage] = useState("");

  async function handleSubmit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();
    setPending(true);
    setErrorMessage("");

    const form = event.currentTarget;
    const formData = new FormData(form);

    try {
      await requestJson("/api/v1/platform-admin-users", {
        method: "POST",
        body: JSON.stringify({
          fullName: String(formData.get("fullName") ?? ""),
          email: String(formData.get("email") ?? ""),
        }),
      });
      form.reset();
      setOpen(false);
      startTransition(() => {
        router.refresh();
      });
    } catch (error) {
      setErrorMessage(
        error instanceof Error ? error.message : "Failed to invite platform admin.",
      );
    } finally {
      setPending(false);
    }
  }

  return (
    <Dialog open={open} onOpenChange={setOpen}>
      <DialogTrigger asChild>
        <Button className="rounded-full">
          <Plus />
          Invite Platform Admin
        </Button>
      </DialogTrigger>
      <DialogContent className="sm:max-w-lg">
        <DialogHeader>
          <DialogTitle>Invite Platform Admin</DialogTitle>
          <DialogDescription>
            Create a platform admin access record. The account links automatically on first verified login.
          </DialogDescription>
        </DialogHeader>
        <form className="space-y-4" onSubmit={handleSubmit}>
          <div className="grid gap-4 sm:grid-cols-2">
            <div className="space-y-2">
              <Label htmlFor="platform-admin-name">Full name</Label>
              <Input
                id="platform-admin-name"
                name="fullName"
                placeholder="Avery Platform"
                required
              />
            </div>
            <div className="space-y-2">
              <Label htmlFor="platform-admin-email">Email</Label>
              <Input id="platform-admin-email" name="email" type="email" required />
            </div>
          </div>
          {errorMessage ? (
            <Alert variant="destructive">
              <AlertTriangle />
              <AlertTitle>Platform admin invite failed</AlertTitle>
              <AlertDescription>{errorMessage}</AlertDescription>
            </Alert>
          ) : null}
          <DialogFooter>
            <Button type="submit" className="rounded-full" disabled={pending}>
              {pending ? "Creating..." : "Create Platform Admin"}
            </Button>
          </DialogFooter>
        </form>
      </DialogContent>
    </Dialog>
  );
}
