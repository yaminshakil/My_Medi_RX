import { Badge } from "@/Components/ui/badge";
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from "@/Components/ui/card";
import AppLayout from "@/Layouts/app-layout";
import { Head, Link } from "@inertiajs/react";
import {
    Activity,
    Building2,
    ChartNoAxesCombined,
    ClipboardCheck,
    FileCheck2,
    MapPin,
    UserRoundCheck,
} from "lucide-react";
import {
    Bar,
    BarChart,
    CartesianGrid,
    ResponsiveContainer,
    Tooltip,
    XAxis,
    YAxis,
} from "recharts";

type StatusTotal = { status: string; total: number };

type AnalyticsProps = {
    summary: {
        pharmacies: {
            total: number;
            draft: number;
            pending: number;
            approved: number;
            rejected: number;
            suspended: number;
        };
        licenses: {
            total: number;
            pending: number;
            verified: number;
            rejected: number;
            expired: number;
        };
        pharmacists: {
            total: number;
            pending: number;
            verified: number;
            rejected: number;
        };
        pendingReviews: number;
    };
    registrationTrend: { month: string; registrations: number }[];
    licenseStatuses: StatusTotal[];
    pharmacistStatuses: StatusTotal[];
    locations: { district: string; division: string | null; total: number }[];
    recentPharmacies: {
        id: number;
        name: string;
        status: string;
        created_at: string | null;
        owner: string | null;
        district: string | null;
        division: string | null;
    }[];
};

const statusClass: Record<string, string> = {
    draft: "border-slate-200 bg-slate-100 text-slate-700",
    pending: "border-amber-200 bg-amber-50 text-amber-800",
    approved: "border-emerald-200 bg-emerald-50 text-emerald-800",
    verified: "border-emerald-200 bg-emerald-50 text-emerald-800",
    rejected: "border-rose-200 bg-rose-50 text-rose-800",
    suspended: "border-orange-200 bg-orange-50 text-orange-800",
    expired: "border-slate-200 bg-slate-100 text-slate-700",
};

const formatDate = (date: string | null) =>
    date
        ? new Intl.DateTimeFormat(undefined, {
              dateStyle: "medium",
          }).format(new Date(date))
        : "—";

function StatusChart({
    title,
    description,
    data,
}: {
    title: string;
    description: string;
    data: StatusTotal[];
}) {
    return (
        <Card>
            <CardHeader>
                <CardTitle>{title}</CardTitle>
                <CardDescription>{description}</CardDescription>
            </CardHeader>
            <CardContent>
                {data.every((item) => item.total === 0) ? (
                    <div className="flex h-64 items-center justify-center text-sm text-muted-foreground">
                        No records yet.
                    </div>
                ) : (
                    <div className="h-64 w-full">
                        <ResponsiveContainer width="100%" height="100%">
                            <BarChart
                                data={data}
                                layout="vertical"
                                margin={{ left: 12, right: 12 }}
                            >
                                <CartesianGrid
                                    strokeDasharray="3 3"
                                    horizontal={false}
                                />
                                <XAxis type="number" allowDecimals={false} />
                                <YAxis
                                    type="category"
                                    dataKey="status"
                                    width={82}
                                    tickLine={false}
                                    axisLine={false}
                                />
                                <Tooltip />
                                <Bar
                                    dataKey="total"
                                    name="Records"
                                    fill="var(--color-primary)"
                                    radius={[0, 4, 4, 0]}
                                />
                            </BarChart>
                        </ResponsiveContainer>
                    </div>
                )}
            </CardContent>
        </Card>
    );
}

export default function Analytics({
    summary,
    registrationTrend,
    licenseStatuses,
    pharmacistStatuses,
    locations,
    recentPharmacies,
}: AnalyticsProps) {
    const metrics = [
        {
            label: "Total pharmacies",
            value: summary.pharmacies.total,
            note: `${summary.pharmacies.approved} approved`,
            icon: Building2,
        },
        {
            label: "Awaiting review",
            value: summary.pendingReviews,
            note: `${summary.pharmacies.pending} pharmacy profiles`,
            icon: ClipboardCheck,
        },
        {
            label: "Verified licenses",
            value: summary.licenses.verified,
            note: `${summary.licenses.pending} pending review`,
            icon: FileCheck2,
        },
        {
            label: "Verified pharmacists",
            value: summary.pharmacists.verified,
            note: `${summary.pharmacists.pending} pending review`,
            icon: UserRoundCheck,
        },
    ];

    return (
        <AppLayout
            breadcrumbs={[
                {
                    title: "Pharmacies",
                    href: route("admin.pharmacies.index"),
                },
                {
                    title: "Analytics",
                    href: route("admin.pharmacies.analytics"),
                },
            ]}
        >
            <Head title="Pharmacy analytics" />
            <div className="flex flex-col gap-6 p-4 md:p-6">
                <div className="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <p className="flex items-center gap-2 text-sm font-medium text-primary">
                            <ChartNoAxesCombined className="size-4" />
                            Healthcare directory
                        </p>
                        <h1 className="mt-1 text-2xl font-semibold tracking-tight">
                            Pharmacy analytics
                        </h1>
                        <p className="mt-1 max-w-2xl text-sm text-muted-foreground">
                            Monitor registrations, credential reviews, and
                            pharmacy coverage across locations.
                        </p>
                    </div>
                    <Link
                        href={route("admin.pharmacies.index")}
                        className="inline-flex h-9 items-center rounded-md border bg-background px-4 text-sm font-medium transition-colors hover:bg-muted"
                    >
                        View pharmacy registry
                    </Link>
                </div>

                <section
                    aria-label="Pharmacy summary"
                    className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4"
                >
                    {metrics.map(({ label, value, note, icon: Icon }) => (
                        <Card key={label}>
                            <CardContent className="flex items-center justify-between gap-4 p-5">
                                <div>
                                    <p className="text-sm text-muted-foreground">
                                        {label}
                                    </p>
                                    <p className="mt-2 text-3xl font-semibold tabular-nums">
                                        {value.toLocaleString()}
                                    </p>
                                    <p className="mt-1 text-xs text-muted-foreground">
                                        {note}
                                    </p>
                                </div>
                                <span className="rounded-lg bg-primary/10 p-3 text-primary">
                                    <Icon className="size-5" />
                                </span>
                            </CardContent>
                        </Card>
                    ))}
                </section>

                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2">
                            <Activity className="size-4 text-primary" />
                            Registration trend
                        </CardTitle>
                        <CardDescription>
                            New pharmacy profiles by month over the last six
                            months.
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        {registrationTrend.every(
                            (item) => item.registrations === 0,
                        ) ? (
                            <div className="flex h-72 items-center justify-center text-sm text-muted-foreground">
                                No pharmacy registrations in this period.
                            </div>
                        ) : (
                            <div className="h-72 w-full">
                                <ResponsiveContainer width="100%" height="100%">
                                    <BarChart
                                        data={registrationTrend}
                                        margin={{
                                            top: 8,
                                            right: 12,
                                            left: 0,
                                            bottom: 4,
                                        }}
                                    >
                                        <CartesianGrid
                                            strokeDasharray="3 3"
                                            vertical={false}
                                        />
                                        <XAxis
                                            dataKey="month"
                                            tickLine={false}
                                            axisLine={false}
                                        />
                                        <YAxis
                                            allowDecimals={false}
                                            tickLine={false}
                                            axisLine={false}
                                        />
                                        <Tooltip />
                                        <Bar
                                            dataKey="registrations"
                                            name="Registrations"
                                            fill="var(--color-primary)"
                                            radius={[4, 4, 0, 0]}
                                        />
                                    </BarChart>
                                </ResponsiveContainer>
                            </div>
                        )}
                    </CardContent>
                </Card>

                <div className="grid gap-4 xl:grid-cols-2">
                    <StatusChart
                        title="Pharmacy licenses"
                        description={`${summary.licenses.total.toLocaleString()} licenses across all pharmacies.`}
                        data={licenseStatuses}
                    />
                    <StatusChart
                        title="Pharmacist credentials"
                        description={`${summary.pharmacists.total.toLocaleString()} registered pharmacist records.`}
                        data={pharmacistStatuses}
                    />
                </div>

                <div className="grid gap-4 xl:grid-cols-2">
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <MapPin className="size-4 text-primary" />
                                Pharmacy coverage
                            </CardTitle>
                            <CardDescription>
                                Districts with the most pharmacy addresses.
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            {locations.length === 0 ? (
                                <p className="py-8 text-center text-sm text-muted-foreground">
                                    No pharmacy locations have been recorded.
                                </p>
                            ) : (
                                <div className="flex flex-col divide-y">
                                    {locations.map((location) => (
                                        <div
                                            key={`${location.division}-${location.district}`}
                                            className="flex items-center justify-between gap-3 py-3"
                                        >
                                            <div>
                                                <p className="font-medium">
                                                    {location.district}
                                                </p>
                                                <p className="text-sm text-muted-foreground">
                                                    {location.division ??
                                                        "Division not provided"}
                                                </p>
                                            </div>
                                            <Badge variant="secondary">
                                                {location.total}{" "}
                                                {location.total === 1
                                                    ? "pharmacy"
                                                    : "pharmacies"}
                                            </Badge>
                                        </div>
                                    ))}
                                </div>
                            )}
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Recent registrations</CardTitle>
                            <CardDescription>
                                The six newest pharmacy profiles.
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            {recentPharmacies.length === 0 ? (
                                <p className="py-8 text-center text-sm text-muted-foreground">
                                    No pharmacies have registered yet.
                                </p>
                            ) : (
                                <div className="flex flex-col divide-y">
                                    {recentPharmacies.map((pharmacy) => (
                                        <div
                                            key={pharmacy.id}
                                            className="flex items-center justify-between gap-3 py-3"
                                        >
                                            <div className="min-w-0">
                                                <Link
                                                    href={route(
                                                        "admin.pharmacies.show",
                                                        pharmacy.id,
                                                    )}
                                                    className="truncate font-medium hover:underline"
                                                >
                                                    {pharmacy.name}
                                                </Link>
                                                <p className="truncate text-sm text-muted-foreground">
                                                    {pharmacy.owner ??
                                                        "Unknown owner"}
                                                    {" · "}
                                                    {[
                                                        pharmacy.district,
                                                        pharmacy.division,
                                                    ]
                                                        .filter(Boolean)
                                                        .join(", ") ||
                                                        "Location not provided"}
                                                </p>
                                            </div>
                                            <div className="flex shrink-0 flex-col items-end gap-1">
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
                                                <span className="text-xs text-muted-foreground">
                                                    {formatDate(
                                                        pharmacy.created_at,
                                                    )}
                                                </span>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            )}
                        </CardContent>
                    </Card>
                </div>
            </div>
        </AppLayout>
    );
}
