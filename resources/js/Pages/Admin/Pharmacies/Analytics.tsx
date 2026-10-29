import { Badge } from "@/Components/ui/badge";
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from "@/Components/ui/card";
import AppLayout from "@/Layouts/app-layout";
import { Head, Link, usePoll } from "@inertiajs/react";
import {
    Activity,
    Building2,
    ChartNoAxesCombined,
    ClipboardCheck,
    Clock3,
    FileCheck2,
    MapPin,
    PackageCheck,
    Pill,
    TriangleAlert,
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

type Operations = {
    todayPrescriptions: number;
    pendingPrescriptions: number;
    medicinesDispensedToday: number;
    lowStockDrugs: number;
    expiringSoon: number;
    lowStockItems: {
        id: number;
        medicine_name: string;
        strength: string | null;
        stock_quantity: number;
        reorder_level: number;
    }[];
    expiringSoonItems: {
        id: number;
        medicine_name: string;
        strength: string | null;
        stock_quantity: number;
        expiry_date: string | null;
    }[];
    recentPrescriptions: {
        id: number;
        prescription_number: string | null;
        status: string;
        received_at: string | null;
    }[];
};

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
    audience: "admin" | "pharmacy";
    operations: Operations | null;
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
    audience,
    operations,
}: AnalyticsProps) {
    usePoll(
        30000,
        { only: ["operations"], preserveScroll: true },
        { autoStart: audience === "pharmacy" },
    );

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
    const operationMetrics = operations
        ? [
              {
                  label: "Prescriptions received today",
                  value: operations.todayPrescriptions,
                  icon: ClipboardCheck,
                  tone: "text-sky-700",
              },
              {
                  label: "Waiting to be fulfilled",
                  value: operations.pendingPrescriptions,
                  icon: Clock3,
                  tone: "text-amber-700",
              },
              {
                  label: "Medicines dispensed today",
                  value: operations.medicinesDispensedToday,
                  icon: PackageCheck,
                  tone: "text-emerald-700",
              },
              {
                  label: "Low-stock drugs",
                  value: operations.lowStockDrugs,
                  icon: Pill,
                  tone: "text-rose-700",
              },
              {
                  label: "Expiring within 30 days",
                  value: operations.expiringSoon,
                  icon: TriangleAlert,
                  tone: "text-orange-700",
              },
          ]
        : [];

    return (
        <AppLayout
            breadcrumbs={
                audience === "pharmacy"
                    ? [{ title: "Pharmacy dashboard", href: route("dashboard") }]
                    : [
                          {
                              title: "Pharmacies",
                              href: route("admin.pharmacies.index"),
                          },
                          {
                              title: "Analytics",
                              href: route("admin.pharmacies.analytics"),
                          },
                      ]
            }
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
                            {audience === "pharmacy"
                                ? "Pharmacy operations"
                                : "Pharmacy analytics"}
                        </h1>
                        <p className="mt-1 max-w-2xl text-sm text-muted-foreground">
                            {audience === "pharmacy"
                                ? "Track prescription fulfillment, dispensing activity, and stock alerts for your pharmacy."
                                : "Monitor registrations, credential reviews, and pharmacy coverage across locations."}
                        </p>
                    </div>
                    {audience === "admin" && (
                        <Link
                            href={route("admin.pharmacies.index")}
                            className="inline-flex h-9 items-center rounded-md border bg-background px-4 text-sm font-medium transition-colors hover:bg-muted"
                        >
                            View pharmacy registry
                        </Link>
                    )}
                </div>

                {audience === "pharmacy" && operations && (
                    <>
                        <section
                            aria-label="Pharmacy operations summary"
                            className="grid gap-4 sm:grid-cols-2 xl:grid-cols-5"
                        >
                            {operationMetrics.map(
                                ({ label, value, icon: Icon, tone }) => (
                                    <Card key={label}>
                                        <CardContent className="flex items-center justify-between gap-4 p-5">
                                            <div>
                                                <p className="text-sm text-muted-foreground">
                                                    {label}
                                                </p>
                                                <p className="mt-2 text-3xl font-semibold tabular-nums">
                                                    {value.toLocaleString()}
                                                </p>
                                            </div>
                                            <span
                                                className={`rounded-lg bg-muted p-3 ${tone}`}
                                            >
                                                <Icon className="size-5" />
                                            </span>
                                        </CardContent>
                                    </Card>
                                ),
                            )}
                        </section>

                        <div className="grid gap-4 xl:grid-cols-3">
                            <Card>
                                <CardHeader>
                                    <CardTitle>Prescription queue</CardTitle>
                                    <CardDescription>
                                        Most recently received prescriptions.
                                    </CardDescription>
                                </CardHeader>
                                <CardContent>
                                    {operations.recentPrescriptions.length ===
                                    0 ? (
                                        <p className="py-6 text-center text-sm text-muted-foreground">
                                            No prescriptions in the queue.
                                        </p>
                                    ) : (
                                        <div className="flex flex-col divide-y">
                                            {operations.recentPrescriptions.map(
                                                (prescription) => (
                                                    <div
                                                        key={prescription.id}
                                                        className="flex items-center justify-between gap-3 py-3"
                                                    >
                                                        <div>
                                                            <p className="font-medium">
                                                                {prescription.prescription_number ??
                                                                    `Prescription #${prescription.id}`}
                                                            </p>
                                                            <p className="text-xs text-muted-foreground">
                                                                {formatDate(
                                                                    prescription.received_at,
                                                                )}
                                                            </p>
                                                        </div>
                                                        <Badge
                                                            variant="outline"
                                                            className={
                                                                statusClass[
                                                                    prescription
                                                                        .status
                                                                ] ?? ""
                                                            }
                                                        >
                                                            {
                                                                prescription.status
                                                            }
                                                        </Badge>
                                                    </div>
                                                ),
                                            )}
                                        </div>
                                    )}
                                </CardContent>
                            </Card>

                            <Card>
                                <CardHeader>
                                    <CardTitle>Low-stock alerts</CardTitle>
                                    <CardDescription>
                                        Stock at or below its reorder level.
                                    </CardDescription>
                                </CardHeader>
                                <CardContent>
                                    {operations.lowStockItems.length === 0 ? (
                                        <p className="py-6 text-center text-sm text-muted-foreground">
                                            No low-stock items.
                                        </p>
                                    ) : (
                                        <div className="flex flex-col divide-y">
                                            {operations.lowStockItems.map(
                                                (item) => (
                                                    <div
                                                        key={item.id}
                                                        className="flex items-center justify-between gap-3 py-3"
                                                    >
                                                        <div>
                                                            <p className="font-medium">
                                                                {
                                                                    item.medicine_name
                                                                }
                                                            </p>
                                                            <p className="text-xs text-muted-foreground">
                                                                {item.strength ??
                                                                    "Strength not provided"}
                                                            </p>
                                                        </div>
                                                        <Badge variant="outline">
                                                            {
                                                                item.stock_quantity
                                                            }{" "}
                                                            /{" "}
                                                            {
                                                                item.reorder_level
                                                            }
                                                        </Badge>
                                                    </div>
                                                ),
                                            )}
                                        </div>
                                    )}
                                </CardContent>
                            </Card>

                            <Card>
                                <CardHeader>
                                    <CardTitle>Near-expiry stock</CardTitle>
                                    <CardDescription>
                                        In-stock items expiring within 30 days.
                                    </CardDescription>
                                </CardHeader>
                                <CardContent>
                                    {operations.expiringSoonItems.length ===
                                    0 ? (
                                        <p className="py-6 text-center text-sm text-muted-foreground">
                                            No items nearing expiration.
                                        </p>
                                    ) : (
                                        <div className="flex flex-col divide-y">
                                            {operations.expiringSoonItems.map(
                                                (item) => (
                                                    <div
                                                        key={item.id}
                                                        className="flex items-center justify-between gap-3 py-3"
                                                    >
                                                        <div>
                                                            <p className="font-medium">
                                                                {
                                                                    item.medicine_name
                                                                }
                                                            </p>
                                                            <p className="text-xs text-muted-foreground">
                                                                {item.strength ??
                                                                    "Strength not provided"}
                                                            </p>
                                                        </div>
                                                        <div className="text-right">
                                                            <p className="font-medium tabular-nums">
                                                                {
                                                                    item.stock_quantity
                                                                }{" "}
                                                                units
                                                            </p>
                                                            <p className="text-xs text-muted-foreground">
                                                                {formatDate(
                                                                    item.expiry_date,
                                                                )}
                                                            </p>
                                                        </div>
                                                    </div>
                                                ),
                                            )}
                                        </div>
                                    )}
                                </CardContent>
                            </Card>
                        </div>
                    </>
                )}

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
