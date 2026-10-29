import { Badge } from "@/Components/ui/badge";
import { Button } from "@/Components/ui/button";
import { Card, CardContent } from "@/Components/ui/card";
import { Input } from "@/Components/ui/input";
import AppLayout from "@/Layouts/app-layout";
import { Head, Link, router } from "@inertiajs/react";
import {
    Building2,
    ChartNoAxesCombined,
    CircleCheck,
    Clock3,
    Plus,
    Search,
    ShieldAlert,
} from "lucide-react";
import { FormEvent, useState } from "react";

type Pharmacy = {
    id: number;
    name: string;
    phone: string | null;
    email: string | null;
    status: string;
    owner: { name: string; email: string } | null;
    address: { district: string | null; division: string | null } | null;
    licenses_count: number;
    pharmacists_count: number;
    created_at: string;
};

type PaginationLink = { url: string | null; label: string; active: boolean };

interface PharmacyIndexProps {
    pharmacies: {
        data: Pharmacy[];
        links: PaginationLink[];
        from: number | null;
        to: number | null;
        total: number;
    };
    stats: {
        total: number;
        pending: number;
        approved: number;
        rejected: number;
        suspended: number;
    };
    filters: { search: string; status: string; per_page: number };
}

const statusClass: Record<string, string> = {
    draft: "border-slate-200 bg-slate-100 text-slate-700",
    pending: "border-amber-200 bg-amber-50 text-amber-800",
    approved: "border-emerald-200 bg-emerald-50 text-emerald-800",
    rejected: "border-rose-200 bg-rose-50 text-rose-800",
    suspended: "border-orange-200 bg-orange-50 text-orange-800",
};

const statsDisplay = [
    { key: "total", label: "Total pharmacies", icon: Building2 },
    { key: "pending", label: "Awaiting review", icon: Clock3 },
    { key: "approved", label: "Approved", icon: CircleCheck },
    { key: "rejected", label: "Rejected / suspended", icon: ShieldAlert },
] as const;

export default function Index({
    pharmacies,
    stats,
    filters,
}: PharmacyIndexProps) {
    const [search, setSearch] = useState(filters.search);

    const visit = (next: Partial<typeof filters>) => {
        router.get(
            route("admin.pharmacies.index"),
            { ...filters, search, ...next },
            { preserveState: true, preserveScroll: true, replace: true },
        );
    };

    const submitSearch = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        visit({ search });
    };

    return (
        <AppLayout
            breadcrumbs={[
                { title: "Pharmacies", href: route("admin.pharmacies.index") },
            ]}
        >
            <Head title="Pharmacy management" />
            <div className="flex flex-col gap-6 p-4 md:p-6">
                <div className="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <p className="text-sm font-medium text-primary">
                            Healthcare directory
                        </p>
                        <h1 className="mt-1 text-2xl font-semibold tracking-tight">
                            Pharmacy management
                        </h1>
                        <p className="mt-1 text-sm text-muted-foreground">
                            Review pharmacy registrations, licenses, and
                            pharmacist credentials.
                        </p>
                    </div>
                    <div className="flex flex-wrap gap-2">
                        <Button variant="outline" asChild>
                            <Link href={route("admin.pharmacies.analytics")}>
                                <ChartNoAxesCombined className="size-4" />{" "}
                                Analytics
                            </Link>
                        </Button>
                        <Button asChild>
                            <Link href={route("admin.pharmacies.create")}>
                                <Plus className="size-4" /> Add pharmacy
                            </Link>
                        </Button>
                    </div>
                </div>

                <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    {statsDisplay.map(({ key, label, icon: Icon }) => (
                        <Card key={key}>
                            <CardContent className="flex items-center justify-between gap-4 p-5">
                                <div>
                                    <p className="text-sm text-muted-foreground">
                                        {label}
                                    </p>
                                    <p className="mt-2 text-2xl font-semibold">
                                        {key === "rejected"
                                            ? stats.rejected + stats.suspended
                                            : stats[key]}
                                    </p>
                                </div>
                                <span className="rounded-lg bg-primary/10 p-3 text-primary">
                                    <Icon className="size-5" />
                                </span>
                            </CardContent>
                        </Card>
                    ))}
                </div>

                <Card>
                    <CardContent className="flex flex-col gap-4 p-4 sm:flex-row sm:items-center">
                        <form
                            onSubmit={submitSearch}
                            className="flex flex-1 gap-2"
                        >
                            <div className="relative flex-1">
                                <Search className="absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground" />
                                <Input
                                    className="pl-9"
                                    value={search}
                                    onChange={(event) =>
                                        setSearch(event.target.value)
                                    }
                                    placeholder="Search pharmacy, owner, email or phone"
                                />
                            </div>
                            <Button type="submit" variant="secondary">
                                Search
                            </Button>
                        </form>
                        <select
                            aria-label="Filter by status"
                            className="h-9 rounded-md border bg-background px-3 text-sm"
                            value={filters.status}
                            onChange={(event) =>
                                visit({ status: event.target.value })
                            }
                        >
                            <option value="">All statuses</option>
                            {[
                                "draft",
                                "pending",
                                "approved",
                                "rejected",
                                "suspended",
                            ].map((status) => (
                                <option key={status} value={status}>
                                    {status[0].toUpperCase() + status.slice(1)}
                                </option>
                            ))}
                        </select>
                        <select
                            aria-label="Rows per page"
                            className="h-9 rounded-md border bg-background px-3 text-sm"
                            value={filters.per_page}
                            onChange={(event) =>
                                visit({ per_page: Number(event.target.value) })
                            }
                        >
                            {[10, 15, 25, 50].map((size) => (
                                <option key={size} value={size}>
                                    {size} rows
                                </option>
                            ))}
                        </select>
                    </CardContent>
                    <div className="overflow-x-auto">
                        <table className="w-full min-w-[850px] text-left text-sm">
                            <thead className="border-y bg-muted/40 text-xs uppercase text-muted-foreground">
                                <tr>
                                    <th className="px-5 py-3 font-medium">
                                        Pharmacy
                                    </th>
                                    <th className="px-5 py-3 font-medium">
                                        Owner
                                    </th>
                                    <th className="px-5 py-3 font-medium">
                                        Location
                                    </th>
                                    <th className="px-5 py-3 font-medium">
                                        Documents
                                    </th>
                                    <th className="px-5 py-3 font-medium">
                                        Status
                                    </th>
                                    <th className="px-5 py-3 text-right font-medium">
                                        Action
                                    </th>
                                </tr>
                            </thead>
                            <tbody className="divide-y">
                                {pharmacies.data.map((pharmacy) => (
                                    <tr
                                        key={pharmacy.id}
                                        className="hover:bg-muted/20"
                                    >
                                        <td className="px-5 py-4">
                                            <Link
                                                href={route(
                                                    "admin.pharmacies.show",
                                                    pharmacy.id,
                                                )}
                                                className="font-medium hover:underline"
                                            >
                                                {pharmacy.name}
                                            </Link>
                                            <p className="mt-1 text-xs text-muted-foreground">
                                                {pharmacy.email ||
                                                    pharmacy.phone ||
                                                    "No contact details"}
                                            </p>
                                        </td>
                                        <td className="px-5 py-4">
                                            <p>
                                                {pharmacy.owner?.name ??
                                                    "Unknown owner"}
                                            </p>
                                            <p className="mt-1 text-xs text-muted-foreground">
                                                {pharmacy.owner?.email}
                                            </p>
                                        </td>
                                        <td className="px-5 py-4 text-muted-foreground">
                                            {[
                                                pharmacy.address?.district,
                                                pharmacy.address?.division,
                                            ]
                                                .filter(Boolean)
                                                .join(", ") || "Not provided"}
                                        </td>
                                        <td className="px-5 py-4 text-muted-foreground">
                                            {pharmacy.licenses_count} licenses ·{" "}
                                            {pharmacy.pharmacists_count}{" "}
                                            pharmacists
                                        </td>
                                        <td className="px-5 py-4">
                                            <Badge
                                                variant="outline"
                                                className={
                                                    statusClass[
                                                        pharmacy.status
                                                    ] ?? ""
                                                }
                                            >
                                                {pharmacy.status}
                                            </Badge>
                                        </td>
                                        <td className="px-5 py-4 text-right">
                                            <Button
                                                asChild
                                                variant="outline"
                                                size="sm"
                                            >
                                                <Link
                                                    href={route(
                                                        "admin.pharmacies.show",
                                                        pharmacy.id,
                                                    )}
                                                >
                                                    Review
                                                </Link>
                                            </Button>
                                        </td>
                                    </tr>
                                ))}
                                {pharmacies.data.length === 0 && (
                                    <tr>
                                        <td
                                            colSpan={6}
                                            className="px-5 py-12 text-center text-muted-foreground"
                                        >
                                            No pharmacies match these filters.
                                        </td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                    </div>
                    <div className="flex flex-col gap-3 border-t px-5 py-4 text-sm text-muted-foreground sm:flex-row sm:items-center sm:justify-between">
                        <span>
                            Showing {pharmacies.from ?? 0}–{pharmacies.to ?? 0}{" "}
                            of {pharmacies.total}
                        </span>
                        <nav
                            aria-label="Pagination"
                            className="flex flex-wrap gap-1"
                        >
                            {pharmacies.links.map((link, index) =>
                                link.url ? (
                                    <Button
                                        key={`${link.label}-${index}`}
                                        asChild
                                        variant={
                                            link.active ? "default" : "outline"
                                        }
                                        size="sm"
                                    >
                                        <Link href={link.url} preserveScroll>
                                            {link.label
                                                .replace("&laquo; ", "")
                                                .replace(" &raquo;", "")}
                                        </Link>
                                    </Button>
                                ) : (
                                    <Button
                                        key={`${link.label}-${index}`}
                                        variant="outline"
                                        size="sm"
                                        disabled
                                    >
                                        {link.label
                                            .replace("&laquo; ", "")
                                            .replace(" &raquo;", "")}
                                    </Button>
                                ),
                            )}
                        </nav>
                    </div>
                </Card>
            </div>
        </AppLayout>
    );
}
