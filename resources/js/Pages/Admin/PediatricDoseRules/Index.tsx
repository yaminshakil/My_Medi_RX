import { Head, Link, router } from "@inertiajs/react";
import { Search, Plus, Eye, Pencil, ShieldCheck } from "lucide-react";
import { useState } from "react";
import RuleStatusBadge from "./components/RuleStatusBadge";
import { Button } from "@/Components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/Components/ui/card";
import { Input } from "@/Components/ui/input";
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from "@/Components/ui/select";
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from "@/Components/ui/table";
import AppLayout from '@/Layouts/app-layout';
import { type BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Pediatric Dose Rules', href: '/admin/pediatric-dose-rules' }];

type Medicine = {
    id: number;
    brand_name: string;
};

type Rule = {
    id: number;
    medicine_id: number;
    medicine?: Medicine | null;
    formulation: string;
    indication: string;
    status: "draft" | "pending_review" | "approved" | "rejected";
    is_active: boolean;
    version: number;
    created_at: string;
};

type PaginationLink = {
    url: string | null;
    label: string;
    active: boolean;
};

type PaginatedRules = {
    data: Rule[];
    current_page: number;
    last_page: number;
    total: number;
    links: PaginationLink[];
};

type Props = {
    rules: PaginatedRules;
    filters: {
        search?: string;
        status?: string;
    };
    stats: {
        total: number;
        draft: number;
        pending: number;
        approved: number;
        rejected: number;
    };
    permissions: {
        create: boolean;
        viewAll: boolean;
        approve: boolean;
        activate: boolean;
    };
};

export default function Index({
    rules,
    filters,
    stats,
    permissions,
}: Props) {
    const [search, setSearch] = useState(filters.search ?? "");
    const [status, setStatus] = useState(filters.status ?? "all");

    function applyFilters() {
        router.get(
            route("admin.pediatric-dose-rules.index"),
            {
                search: search || undefined,
                status: status === "all" ? undefined : status,
            },
            {
                preserveState: true,
                replace: true,
            },
        );
    }

    const summary = [
        { label: "Total Rules", value: stats.total },
        { label: "Drafts", value: stats.draft },
        { label: "Pending Review", value: stats.pending },
        { label: "Approved", value: stats.approved },
        { label: "Rejected", value: stats.rejected },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Pediatric Dose Rules" />

            <div className="space-y-6 p-4 md:p-6">
                <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h1 className="text-2xl font-semibold tracking-tight">
                            Pediatric Dose Rules
                        </h1>
                        <p className="text-sm text-muted-foreground">
                            Manage clinical dosing rules and their approval workflow.
                        </p>
                    </div>

                    {permissions.create && (
                        <Button asChild>
                            <Link href={route("admin.pediatric-dose-rules.create")}>
                                <Plus className="mr-2 h-4 w-4" />
                                Create Rule
                            </Link>
                        </Button>
                    )}
                </div>

                <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
                    {summary.map((item) => (
                        <Card key={item.label}>
                            <CardHeader className="pb-2">
                                <CardTitle className="text-sm font-medium text-muted-foreground">
                                    {item.label}
                                </CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div className="text-2xl font-semibold">
                                    {item.value}
                                </div>
                            </CardContent>
                        </Card>
                    ))}
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>Rule Registry</CardTitle>
                    </CardHeader>

                    <CardContent className="space-y-4">
                        <div className="flex flex-col gap-3 md:flex-row">
                            <div className="relative flex-1">
                                <Search className="absolute left-3 top-2.5 h-4 w-4 text-muted-foreground" />
                                <Input
                                    value={search}
                                    onChange={(event) => setSearch(event.target.value)}
                                    onKeyDown={(event) => {
                                        if (event.key === "Enter") applyFilters();
                                    }}
                                    placeholder="Search medicine, formulation, indication..."
                                    className="pl-9"
                                />
                            </div>

                            <Select value={status} onValueChange={setStatus}>
                                <SelectTrigger className="w-full md:w-[200px]">
                                    <SelectValue placeholder="Filter status" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">All statuses</SelectItem>
                                    <SelectItem value="draft">Draft</SelectItem>
                                    <SelectItem value="pending_review">
                                        Pending review
                                    </SelectItem>
                                    <SelectItem value="approved">Approved</SelectItem>
                                    <SelectItem value="rejected">Rejected</SelectItem>
                                </SelectContent>
                            </Select>

                            <Button onClick={applyFilters}>Search</Button>
                        </div>

                        <div className="overflow-x-auto rounded-md border">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Medicine</TableHead>
                                        <TableHead>Formulation</TableHead>
                                        <TableHead>Indication</TableHead>
                                        <TableHead>Status</TableHead>
                                        <TableHead>Active</TableHead>
                                        <TableHead>Version</TableHead>
                                        <TableHead className="text-right">
                                            Actions
                                        </TableHead>
                                    </TableRow>
                                </TableHeader>

                                <TableBody>
                                    {rules.data.length === 0 ? (
                                        <TableRow>
                                            <TableCell
                                                colSpan={7}
                                                className="h-28 text-center text-muted-foreground"
                                            >
                                                No pediatric dose rules found.
                                            </TableCell>
                                        </TableRow>
                                    ) : (
                                        rules.data.map((rule) => (
                                            <TableRow key={rule.id}>
                                                <TableCell className="font-medium">
                                                    {rule.medicine?.brand_name ??
                                                        `Medicine #${rule.medicine_id}`}
                                                </TableCell>
                                                <TableCell>{rule.formulation}</TableCell>
                                                <TableCell>{rule.indication}</TableCell>
                                                <TableCell>
                                                    <RuleStatusBadge status={rule.status} />
                                                </TableCell>
                                                <TableCell>
                                                    {rule.is_active ? "Yes" : "No"}
                                                </TableCell>
                                                <TableCell>v{rule.version}</TableCell>
                                                <TableCell>
                                                    <div className="flex justify-end gap-2">
                                                        <Button
                                                            asChild
                                                            size="icon"
                                                            variant="outline"
                                                            aria-label="View rule"
                                                        >
                                                            <Link
                                                                href={route(
                                                                    "admin.pediatric-dose-rules.show",
                                                                    rule.id,
                                                                )}
                                                            >
                                                                <Eye className="h-4 w-4" />
                                                            </Link>
                                                        </Button>

                                                        {["draft", "rejected"].includes(
                                                            rule.status,
                                                        ) && (
                                                                <Button
                                                                    asChild
                                                                    size="icon"
                                                                    variant="outline"
                                                                    aria-label="Edit rule"
                                                                >
                                                                    <Link
                                                                        href={route(
                                                                            "admin.pediatric-dose-rules.edit",
                                                                            rule.id,
                                                                        )}
                                                                    >
                                                                        <Pencil className="h-4 w-4" />
                                                                    </Link>
                                                                </Button>
                                                            )}

                                                        {permissions.approve &&
                                                            rule.status ===
                                                            "pending_review" && (
                                                                <Button
                                                                    asChild
                                                                    size="icon"
                                                                    variant="outline"
                                                                    aria-label="Review rule"
                                                                >
                                                                    <Link
                                                                        href={route(
                                                                            "admin.pediatric-dose-rules.show",
                                                                            rule.id,
                                                                        )}
                                                                    >
                                                                        <ShieldCheck className="h-4 w-4" />
                                                                    </Link>
                                                                </Button>
                                                            )}
                                                    </div>
                                                </TableCell>
                                            </TableRow>
                                        ))
                                    )}
                                </TableBody>
                            </Table>
                        </div>

                        <div className="flex flex-col gap-3 text-sm text-muted-foreground sm:flex-row sm:items-center sm:justify-between">
                            <span>
                                Page {rules.current_page} of {rules.last_page} ·{" "}
                                {rules.total} rules
                            </span>

                            <div className="flex flex-wrap gap-1">
                                {rules.links.map((link, index) => (
                                    <Button
                                        key={`${link.label}-${index}`}
                                        size="sm"
                                        variant={link.active ? "default" : "outline"}
                                        disabled={!link.url}
                                        onClick={() => {
                                            if (link.url) {
                                                router.visit(link.url, {
                                                    preserveScroll: true,
                                                    preserveState: true,
                                                });
                                            }
                                        }}
                                    >
                                        <span
                                            dangerouslySetInnerHTML={{
                                                __html: link.label,
                                            }}
                                        />
                                    </Button>
                                ))}
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
