import type { LucideIcon } from "lucide-react";
import { Badge } from "@/components/ui/badge";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";

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
    <Card className="border-border/70 bg-card/90 shadow-sm">
      <CardHeader className="flex flex-row items-start justify-between space-y-0">
        <div className="space-y-1">
          <CardTitle>{title}</CardTitle>
          <p className="text-sm leading-6 text-muted-foreground">{description}</p>
        </div>
        <div className="rounded-2xl border border-border/70 bg-background/80 p-3 text-primary">
          <Icon className="size-5" />
        </div>
      </CardHeader>
      <CardContent className="text-4xl font-semibold tracking-tight">
        {value}
      </CardContent>
    </Card>
  );
}

export function DashboardStatusBadge({ value }: { value: string }) {
  const variant =
    statusVariantMap[value as keyof typeof statusVariantMap] ?? "outline";

  return (
    <Badge variant={variant} className="rounded-full uppercase tracking-[0.2em]">
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
    <div className="rounded-2xl border border-dashed border-border/80 bg-background/60 p-6 text-sm">
      <p className="font-medium">{title}</p>
      <p className="mt-2 leading-6 text-muted-foreground">{description}</p>
    </div>
  );
}
