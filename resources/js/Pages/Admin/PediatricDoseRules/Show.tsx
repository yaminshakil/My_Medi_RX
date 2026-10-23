import { Head, Link, router, usePage } from "@inertiajs/react";
import { CheckCircle, XCircle, Send, Power, PowerOff, Pencil } from "lucide-react";

import RuleStatusBadge from "./components/RuleStatusBadge";

import { Button } from "@/Components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/Components/ui/card";
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
    AlertDialogTrigger,
} from "@/Components/ui/alert-dialog";
import { Separator } from "@/Components/ui/separator";
import AppLayout from '@/Layouts/app-layout';

type Rule = {
    id: number;
    medicine?: { brand_name: string } | null;
    formulation: string;
    indication: string;
    concentration_mg: string | number;
    concentration_ml: string | number;
    dose_mg_per_kg_per_dose: string | number;
    max_single_dose_mg?: string | number | null;
    max_daily_mg_per_kg?: string | number | null;
    max_daily_dose_mg?: string | number | null;
    dose_interval_hours?: number | null;
    max_doses_per_24_hours?: number | null;
    min_age_months: number;
    max_age_months?: number | null;
    min_weight_kg?: string | number | null;
    max_weight_kg?: string | number | null;
    route: string;
    source_reference: string;
    source_version?: string | null;
    contraindications?: string | null;
    notes?: string | null;
    status: "draft" | "pending_review" | "approved" | "rejected";
    is_active: boolean;
    version: number;
    rejection_reason?: string | null;
    creator?: { name: string } | null;
    reviewer?: { name: string } | null;
};

type Audit = {
    id: number;
    action: string;
    from_status?: string | null;
    to_status?: string | null;
    reason?: string | null;
    created_at: string;
    user?: { name: string } | null;
};

type Props = {
    rule: Rule;
    audits: {
        data: Audit[];
    };
};

function Detail({
    label,
    value,
}: {
    label: string;
    value?: string | number | null;
}) {
    return (
        <div className="space-y-1">
            <p className="text-sm text-muted-foreground">{label}</p>
            <p className="break-words font-medium">{value ?? "—"}</p>
        </div>
    );
}

export default function Show({ rule, audits }: Props) {
    const { auth } = usePage().props as {
        auth?: { user?: { permissions?: string[] } };
    };

    const permissions = auth?.permissions ?? [];
    const canApprove = permissions.includes("pediatric-dose-rules.approve");
    const canActivate = permissions.includes("pediatric-dose-rules.activate");
    const canEdit = ["draft", "rejected"].includes(rule.status);

    function postAction(
        action: "submit" | "approve" | "activate" | "deactivate",
        data: Record<string, string> = {},
    ) {
        router.post(
            route(`admin.pediatric-dose-rules.${action}`, rule.id),
            data,
            {
                preserveScroll: true,
                onSuccess: () => {
                    // Inertia refreshes the page props after a successful redirect.
                },
                onError: (errors) => {
                    console.error(`${action} failed:`, errors);
                },
            },
        );
    }

    function rejectRule() {
        const reason = window.prompt(
            "Enter the reason for rejecting this clinical rule:",
        );

        if (!reason || reason.trim().length < 10) {
            return;
        }

        router.post(
            route("admin.pediatric-dose-rules.reject", rule.id),
            { reason },
        );
    }
    console.log(rule.medicine);
    return (
        <AppLayout>
            <Head title={`Pediatric Dose Rule #${rule.id}`} />

            <div className="mx-auto w-full space-y-6 p-4 md:p-6">
                <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div className="space-y-2">
                        <div className="flex flex-wrap items-center gap-3">
                            <h1 className="text-2xl font-semibold">
                                {rule.medicine?.brand_name ?? "Pediatric Dose Rule"}
                            </h1>
                            <RuleStatusBadge status={rule.status} />
                        </div>

                        <p className="text-sm text-muted-foreground">
                            Rule #{rule.id} · Version {rule.version}
                        </p>
                    </div>

                    <div className="flex flex-wrap gap-2">
                        {canEdit && (
                            <Button asChild variant="outline">
                                <Link
                                    href={route(
                                        "admin.pediatric-dose-rules.edit",
                                        rule.id,
                                    )}
                                >
                                    <Pencil className="mr-2 h-4 w-4" />
                                    Edit
                                </Link>
                            </Button>
                        )}

                        {rule.status === "draft" ||
                            rule.status === "rejected" ? (
                            <Button onClick={() => postAction("submit")}>
                                <Send className="mr-2 h-4 w-4" />
                                Submit for Review
                            </Button>
                        ) : null}

                        {canApprove && rule.status === "pending_review" && (
                            <>
                                <Button onClick={() => postAction("approve")}>
                                    <CheckCircle className="mr-2 h-4 w-4" />
                                    Approve
                                </Button>

                                <Button variant="destructive" onClick={rejectRule}>
                                    <XCircle className="mr-2 h-4 w-4" />
                                    Reject
                                </Button>
                            </>
                        )}

                        {canActivate &&
                            rule.status === "approved" &&
                            !rule.is_active && (
                                <Button onClick={() => postAction("activate")}>
                                    <Power className="mr-2 h-4 w-4" />
                                    Activate
                                </Button>
                            )}

                        {canActivate &&
                            rule.status === "approved" &&
                            rule.is_active && (
                                <Button
                                    variant="destructive"
                                    onClick={() => postAction("deactivate")}
                                >
                                    <PowerOff className="mr-2 h-4 w-4" />
                                    Deactivate
                                </Button>
                            )}
                    </div>
                </div>

                {rule.rejection_reason && (
                    <Card className="border-destructive/50">
                        <CardHeader>
                            <CardTitle className="text-base">
                                Rejection Reason
                            </CardTitle>
                        </CardHeader>
                        <CardContent>{rule.rejection_reason}</CardContent>
                    </Card>
                )}

                <Card>
                    <CardHeader>
                        <CardTitle>Medicine and Eligibility</CardTitle>
                    </CardHeader>
                    <CardContent className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                        <Detail label="Medicine" value={rule.medicine?.brand_name} />
                        <Detail label="Formulation" value={rule.formulation} />
                        <Detail label="Indication" value={rule.indication} />
                        <Detail label="Route" value={rule.route} />
                        <Detail label="Minimum age (months)" value={rule.min_age_months} />
                        <Detail label="Maximum age (months)" value={rule.max_age_months} />
                        <Detail label="Minimum weight (kg)" value={rule.min_weight_kg} />
                        <Detail label="Maximum weight (kg)" value={rule.max_weight_kg} />
                        <Detail label="Active" value={rule.is_active ? "Yes" : "No"} />
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Dose Parameters</CardTitle>
                    </CardHeader>
                    <CardContent className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                        <Detail
                            label="Concentration (mg)"
                            value={rule.concentration_mg}
                        />
                        <Detail
                            label="Concentration volume (mL)"
                            value={rule.concentration_ml}
                        />
                        <Detail
                            label="Approved mg/kg per dose"
                            value={rule.dose_mg_per_kg_per_dose}
                        />
                        <Detail
                            label="Maximum single dose (mg)"
                            value={rule.max_single_dose_mg}
                        />
                        <Detail
                            label="Maximum daily mg/kg"
                            value={rule.max_daily_mg_per_kg}
                        />
                        <Detail
                            label="Maximum daily dose (mg)"
                            value={rule.max_daily_dose_mg}
                        />
                        <Detail
                            label="Interval (hours)"
                            value={rule.dose_interval_hours}
                        />
                        <Detail
                            label="Maximum doses per 24 hours"
                            value={rule.max_doses_per_24_hours}
                        />
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Clinical Source and Notes</CardTitle>
                    </CardHeader>
                    <CardContent className="space-y-4">
                        <Detail label="Source reference" value={rule.source_reference} />
                        <Detail label="Source version" value={rule.source_version} />
                        <Separator />
                        <Detail
                            label="Contraindications and warnings"
                            value={rule.contraindications}
                        />
                        <Detail label="Notes" value={rule.notes} />
                        <Separator />
                        <Detail label="Created by" value={rule.creator?.name} />
                        <Detail label="Reviewed by" value={rule.reviewer?.name} />
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Audit History</CardTitle>
                    </CardHeader>
                    <CardContent className="space-y-4">
                        {audits.data.length === 0 ? (
                            <p className="text-sm text-muted-foreground">
                                No audit events recorded yet.
                            </p>
                        ) : (
                            audits.data.map((audit) => (
                                <div
                                    key={audit.id}
                                    className="flex gap-3 border-b pb-4 last:border-0"
                                >
                                    <div className="mt-1 h-2 w-2 rounded-full bg-primary" />
                                    <div className="min-w-0 space-y-1">
                                        <p className="font-medium">
                                            {audit.action.replaceAll("_", " ")}
                                        </p>
                                        <p className="text-sm text-muted-foreground">
                                            {audit.user?.name ?? "System"} ·{" "}
                                            {new Date(audit.created_at).toLocaleString()}
                                        </p>
                                        {audit.from_status && (
                                            <p className="text-sm">
                                                {audit.from_status} → {audit.to_status}
                                            </p>
                                        )}
                                        {audit.reason && (
                                            <p className="text-sm">{audit.reason}</p>
                                        )}
                                    </div>
                                </div>
                            ))
                        )}
                    </CardContent>
                </Card>

                <div>
                    <Button asChild variant="outline">
                        <Link href={route("admin.pediatric-dose-rules.index")}>
                            Back to Rules
                        </Link>
                    </Button>
                </div>
            </div>
        </AppLayout>
    );
}
