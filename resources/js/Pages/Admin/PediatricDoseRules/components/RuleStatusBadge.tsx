import { Badge } from "@/components/ui/badge";

type RuleStatus =
    | "draft"
    | "pending_review"
    | "approved"
    | "rejected";

const statusConfig: Record<
    RuleStatus,
    { label: string; className: string }
> = {
    draft: {
        label: "Draft",
        className: "bg-muted text-muted-foreground",
    },
    pending_review: {
        label: "Pending Review",
        className: "bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-200",
    },
    approved: {
        label: "Approved",
        className: "bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-200",
    },
    rejected: {
        label: "Rejected",
        className: "bg-red-100 text-red-800 dark:bg-red-950 dark:text-red-200",
    },
};

export default function RuleStatusBadge({
    status,
}: {
    status: RuleStatus;
}) {
    const config = statusConfig[status] ?? statusConfig.draft;

    return (
        <Badge variant="outline" className={config.className}>
            {config.label}
        </Badge>
    );
}
