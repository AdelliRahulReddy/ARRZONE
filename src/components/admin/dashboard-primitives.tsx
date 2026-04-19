import type { ReactNode } from "react";
import type { LucideIcon } from "lucide-react";
import { Badge } from "@/components/ui/badge";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { cn } from "@/lib/utils";

const statusVariantMap = {
  ACTIVE: "default",
  INVITED: "secondary",
  INACTIVE: "secondary",
  DISABLED: "destructive",
  SUSPENDED: "destructive",
  ARCHIVED: "outline",
} as const;

type AdminMetricCardProps = {
  title: string;
  value: number | string;
  description: string;
  icon: LucideIcon;
};

export function AdminMetricCard({
  title,
  value,
  description,
  icon: Icon,
}: AdminMetricCardProps) {
  return (
    <Card className="overflow-hidden rounded-[1.75rem] border border-border/60 bg-card/95 shadow-[0_24px_70px_-42px_rgba(15,23,42,0.18)]">
      <CardHeader className="relative flex flex-row items-start justify-between space-y-0 gap-4">
        <div className="space-y-1">
          <p className="text-xs font-medium uppercase tracking-[0.24em] text-muted-foreground">
            Snapshot
          </p>
          <CardTitle className="text-base">{title}</CardTitle>
          <p className="text-sm leading-6 text-muted-foreground">{description}</p>
        </div>
        <div className="rounded-[1.35rem] border border-border/60 bg-background/80 p-3 text-primary shadow-sm">
          <Icon className="size-5" />
        </div>
      </CardHeader>
      <CardContent className="pb-6 text-4xl font-semibold tracking-tight">
        {value}
      </CardContent>
    </Card>
  );
}

export function DashboardStatusBadge({ value }: { value: string }) {
  const variant =
    statusVariantMap[value as keyof typeof statusVariantMap] ?? "outline";

  return (
    <Badge
      variant={variant}
      className="rounded-full border border-border/50 px-3 py-1 uppercase tracking-[0.2em]"
    >
      {value.replaceAll("_", " ")}
    </Badge>
  );
}

export function DashboardEmptyState({
  title,
  description,
}: {
  title: string;
  description: string;
}) {
  return (
    <div className="rounded-[1.6rem] border border-dashed border-border/70 bg-background/70 p-6 text-sm shadow-[inset_0_1px_0_rgba(255,255,255,0.72)]">
      <p className="font-medium">{title}</p>
      <p className="mt-2 leading-6 text-muted-foreground">{description}</p>
    </div>
  );
}

type DashboardHeroStat = {
  label: string;
  value: number | string;
};

export function DashboardHero({
  eyebrow,
  title,
  description,
  actions,
  stats = [],
  className,
}: {
  eyebrow: string;
  title: string;
  description: string;
  actions?: ReactNode;
  stats?: DashboardHeroStat[];
  className?: string;
}) {
  return (
    <section
      className={cn(
        "relative overflow-hidden rounded-[2.2rem] border border-border/60 bg-[linear-gradient(180deg,rgba(255,255,255,0.92),rgba(248,244,236,0.82))] px-6 py-7 shadow-[0_30px_90px_-48px_rgba(38,59,104,0.32)] backdrop-blur sm:px-8 sm:py-8",
        "dark:bg-[linear-gradient(180deg,rgba(38,43,52,0.92),rgba(24,29,38,0.86))]",
        className,
      )}
    >
      <div className="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-primary/45 to-transparent" />
      <div className="absolute -right-16 top-0 size-40 rounded-full bg-primary/10 blur-3xl" />
      <div className="absolute left-0 top-1/2 size-32 -translate-x-1/2 -translate-y-1/2 rounded-full bg-amber-300/15 blur-3xl" />

      <div className="relative flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
        <div className="max-w-3xl space-y-3">
          <p className="text-xs font-medium uppercase tracking-[0.32em] text-muted-foreground">
            {eyebrow}
          </p>
          <h1 className="max-w-2xl text-3xl font-semibold tracking-tight text-balance sm:text-4xl lg:text-[2.8rem]">
            {title}
          </h1>
          <p className="max-w-2xl text-base leading-7 text-muted-foreground sm:text-lg">
            {description}
          </p>
        </div>
        {actions ? (
          <div className="relative flex shrink-0 items-center gap-3 self-start lg:self-end">
            {actions}
          </div>
        ) : null}
      </div>

      {stats.length > 0 ? (
        <div className="relative mt-8 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
          {stats.map((stat) => (
            <div
              key={stat.label}
              className="rounded-[1.35rem] border border-border/50 bg-background/72 px-4 py-3 shadow-[inset_0_1px_0_rgba(255,255,255,0.72)] backdrop-blur"
            >
              <p className="text-xs uppercase tracking-[0.24em] text-muted-foreground">
                {stat.label}
              </p>
              <p className="mt-2 text-2xl font-semibold tracking-tight">{stat.value}</p>
            </div>
          ))}
        </div>
      ) : null}
    </section>
  );
}
