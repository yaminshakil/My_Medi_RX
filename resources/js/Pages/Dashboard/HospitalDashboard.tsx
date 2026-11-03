import AppLayout from "@/Layouts/app-layout";
import { type BreadcrumbItem } from "@/types";
import { Head } from "@inertiajs/react";
import {
    Activity,
    BadgeCheck,
    Building2,
    CalendarDays,
    Clock3,
    MapPin,
    Stethoscope,
    UsersRound,
} from "lucide-react";
import type { LucideIcon } from "lucide-react";

type Hospital = {
    name: string;
    address: string;
    email: string | null;
    phone: string | null;
    verificationStatus: string;
};

type Chamber = {
    id: number;
    name: string;
    city: string;
    address: string;
    fee: string;
    isActive: boolean;
    doctor: {
        name: string;
        email: string;
    } | null;
};

type HospitalDashboardProps = {
    hospital: Hospital | null;
    stats: {
        totalDoctors: number;
        totalChambers: number;
        activeChambers: number;
        todayAppointments: number;
        pendingAppointments: number;
    };
    chambers: Chamber[];
};

type StatCard = {
    label: string;
    value: number;
    description: string;
    icon: LucideIcon;
    iconClass: string;
};

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: "Dashboard",
        href: "/admin/dashboard",
    },
];

const verificationLabels: Record<string, string> = {
    approved: "Verified",
    under_review: "Under review",
    rejected: "Verification rejected",
    suspended: "Suspended",
    pending: "Verification pending",
};

function formatFee(fee: string): string {
    return `BDT ${Number(fee).toLocaleString("en-BD")}`;
}

export default function HospitalDashboard({
    hospital,
    stats,
    chambers,
}: HospitalDashboardProps) {
    const statCards: StatCard[] = [
        {
            label: "Doctors",
            value: stats.totalDoctors,
            description: "Doctors across your chambers",
            icon: UsersRound,
            iconClass:
                "bg-sky-100 text-sky-700 dark:bg-sky-950 dark:text-sky-300",
        },
        {
            label: "Chambers",
            value: stats.totalChambers,
            description: "Total hospital chambers",
            icon: Building2,
            iconClass:
                "bg-violet-100 text-violet-700 dark:bg-violet-950 dark:text-violet-300",
        },
        {
            label: "Active chambers",
            value: stats.activeChambers,
            description: "Currently open for appointments",
            icon: Activity,
            iconClass:
                "bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300",
        },
        {
            label: "Today's appointments",
            value: stats.todayAppointments,
            description: "Scheduled for today",
            icon: CalendarDays,
            iconClass:
                "bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-300",
        },
        {
            label: "Pending appointments",
            value: stats.pendingAppointments,
            description: "Awaiting confirmation",
            icon: Clock3,
            iconClass:
                "bg-rose-100 text-rose-700 dark:bg-rose-950 dark:text-rose-300",
        },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Hospital Dashboard" />
            <main className="flex flex-1 flex-col gap-6 p-4 md:p-6">
                <section className="overflow-hidden rounded-2xl bg-gradient-to-r from-teal-800 via-teal-700 to-cyan-700 p-6 text-white shadow-sm md:p-8">
                    <div className="flex flex-col gap-5 md:flex-row md:items-center md:justify-between">
                        <div className="flex items-start gap-4">
                            <div className="rounded-xl bg-white/15 p-3">
                                <Building2
                                    className="size-7"
                                    aria-hidden="true"
                                />
                            </div>
                            <div>
                                <p className="text-sm font-medium text-teal-100">
                                    Hospital overview
                                </p>
                                <h1 className="mt-1 text-2xl font-semibold tracking-tight md:text-3xl">
                                    {hospital?.name ?? "Hospital dashboard"}
                                </h1>
                                <p className="mt-2 max-w-2xl text-sm text-teal-50/90">
                                    {hospital
                                        ? hospital.address
                                        : "No hospital profile is currently linked to your account."}
                                </p>
                            </div>
                        </div>
                        {hospital && (
                            <div className="flex w-fit items-center gap-2 rounded-full bg-white/15 px-3 py-2 text-sm font-medium">
                                <BadgeCheck
                                    className="size-4"
                                    aria-hidden="true"
                                />
                                {verificationLabels[
                                    hospital.verificationStatus
                                ] ?? "Verification pending"}
                            </div>
                        )}
                    </div>
                    {hospital && (hospital.email || hospital.phone) && (
                        <div className="mt-5 flex flex-wrap gap-x-6 gap-y-2 border-t border-white/15 pt-4 text-sm text-teal-50/90">
                            {hospital.email && <span>{hospital.email}</span>}
                            {hospital.phone && <span>{hospital.phone}</span>}
                        </div>
                    )}
                </section>

                <section
                    aria-label="Hospital statistics"
                    className="grid gap-4 sm:grid-cols-2 xl:grid-cols-5"
                >
                    {statCards.map(
                        ({
                            label,
                            value,
                            description,
                            icon: Icon,
                            iconClass,
                        }) => (
                            <article
                                key={label}
                                className="rounded-xl border border-sidebar-border/70 bg-white p-5 shadow-sm dark:border-sidebar-border dark:bg-neutral-900"
                            >
                                <div className="flex items-start justify-between gap-3">
                                    <div>
                                        <p className="text-sm font-medium text-muted-foreground">
                                            {label}
                                        </p>
                                        <p className="mt-2 text-3xl font-semibold tracking-tight text-foreground">
                                            {value}
                                        </p>
                                    </div>
                                    <div
                                        className={`rounded-lg p-2.5 ${iconClass}`}
                                    >
                                        <Icon
                                            className="size-5"
                                            aria-hidden="true"
                                        />
                                    </div>
                                </div>
                                <p className="mt-3 text-xs text-muted-foreground">
                                    {description}
                                </p>
                            </article>
                        ),
                    )}
                </section>

                <section className="rounded-2xl border border-sidebar-border/70 bg-white shadow-sm dark:border-sidebar-border dark:bg-neutral-900">
                    <div className="flex flex-col gap-1 border-b border-sidebar-border/70 px-5 py-4 dark:border-sidebar-border sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h2 className="text-lg font-semibold text-foreground">
                                Chambers and doctors
                            </h2>
                            <p className="text-sm text-muted-foreground">
                                Doctors connected to your hospital through their
                                chambers.
                            </p>
                        </div>
                        <span className="text-sm text-muted-foreground">
                            {chambers.length} chambers
                        </span>
                    </div>

                    {chambers.length > 0 ? (
                        <div className="grid gap-4 p-4 md:grid-cols-2 xl:grid-cols-3">
                            {chambers.map((chamber) => (
                                <article
                                    key={chamber.id}
                                    className="rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border"
                                >
                                    <div className="flex items-start justify-between gap-3">
                                        <div className="flex min-w-0 items-center gap-3">
                                            <div className="rounded-lg bg-teal-50 p-2.5 text-teal-700 dark:bg-teal-950 dark:text-teal-300">
                                                <Stethoscope
                                                    className="size-5"
                                                    aria-hidden="true"
                                                />
                                            </div>
                                            <div className="min-w-0">
                                                <h3 className="truncate font-semibold text-foreground">
                                                    {chamber.name}
                                                </h3>
                                                <p className="mt-0.5 truncate text-sm text-muted-foreground">
                                                    {chamber.doctor?.name ??
                                                        "Doctor not assigned"}
                                                </p>
                                            </div>
                                        </div>
                                        <span
                                            className={`shrink-0 rounded-full px-2.5 py-1 text-xs font-medium ${
                                                chamber.isActive
                                                    ? "bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300"
                                                    : "bg-neutral-100 text-neutral-700 dark:bg-neutral-800 dark:text-neutral-300"
                                            }`}
                                        >
                                            {chamber.isActive
                                                ? "Active"
                                                : "Inactive"}
                                        </span>
                                    </div>

                                    <div className="mt-4 flex flex-col gap-2 border-t border-sidebar-border/70 pt-3 text-sm text-muted-foreground dark:border-sidebar-border">
                                        <p className="flex items-start gap-2">
                                            <MapPin
                                                className="mt-0.5 size-4 shrink-0"
                                                aria-hidden="true"
                                            />
                                            <span>
                                                {[chamber.address, chamber.city]
                                                    .filter(Boolean)
                                                    .join(", ") ||
                                                    "No address provided"}
                                            </span>
                                        </p>
                                        {chamber.doctor?.email && (
                                            <p className="truncate pl-6">
                                                {chamber.doctor.email}
                                            </p>
                                        )}
                                        <p className="pl-6 font-medium text-foreground">
                                            {formatFee(chamber.fee)}{" "}
                                            consultation fee
                                        </p>
                                    </div>
                                </article>
                            ))}
                        </div>
                    ) : (
                        <div className="flex flex-col items-center px-6 py-12 text-center">
                            <div className="rounded-full bg-teal-50 p-3 text-teal-700 dark:bg-teal-950 dark:text-teal-300">
                                <Building2
                                    className="size-6"
                                    aria-hidden="true"
                                />
                            </div>
                            <h3 className="mt-4 font-semibold text-foreground">
                                {hospital
                                    ? "No chambers linked yet"
                                    : "Hospital profile not found"}
                            </h3>
                            <p className="mt-1 max-w-md text-sm text-muted-foreground">
                                {hospital
                                    ? "Once doctors are connected to your hospital, their chambers will appear here."
                                    : "Ask your administrator to link a hospital profile to your account."}
                            </p>
                        </div>
                    )}
                </section>
            </main>
        </AppLayout>
    );
}
