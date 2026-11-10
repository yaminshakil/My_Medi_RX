import AppLayout from "@/Layouts/app-layout";
import { type AppPageProps, type BreadcrumbItem } from "@/types";
import { Head, Link, usePage } from "@inertiajs/react";
import {
    Activity,
    Baby,
    CalendarDays,
    CheckCircle2,
    Clock3,
    HeartPulse,
    Pill,
} from "lucide-react";
import type { LucideIcon } from "lucide-react";

type PatientDashboardInfo = {
    name: string;
    totalappoinments: number;
    newappointments: number;
    upcomingAppointments: number;
    totalprescriptions: number;
    activePrescriptions: number;
    medications: {
        active: number;
        today: {
            total: number;
            taken: number;
            missed: number;
            upcoming: number;
            skipped: number;
            snoozed: number;
        };
        adherence: {
            total: number;
            taken: number;
            missed: number;
            skipped: number;
            percentage: number | null;
        };
    };
    vitals: {
        recordedAt: string | null;
        bloodPressure: string | null;
        heartRate: number | null;
        oxygenSaturation: number | null;
        temperature: number | string | null;
        weight: number | string | null;
        bmi: number | string | null;
    };
    pregnancy: {
        status: "not_started" | "inactive" | "needs_review" | "active";
        currentWeek?: number;
        daysToGo?: number;
        estimatedDueDate?: string;
    };
    cycle: {
        status: "not_started" | "inactive" | "active";
        currentCycleDay?: number | null;
        phase?: string;
        nextPeriod?: string | null;
    };
};

type SummaryCardProps = {
    title: string;
    value: number;
    description: string;
    icon: LucideIcon;
    href: string;
};

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: "Dashboard",
        href: "/admin/dashboard",
    },
];

function SummaryCard({
    title,
    value,
    description,
    icon: Icon,
    href,
}: SummaryCardProps) {
    return (
        <Link
            href={href}
            className="group rounded-xl border border-sidebar-border/70 bg-card p-5 transition hover:border-primary/40 hover:shadow-sm dark:border-sidebar-border"
        >
            <div className="flex items-start justify-between gap-4">
                <div>
                    <p className="text-sm font-medium text-muted-foreground">
                        {title}
                    </p>
                    <p className="mt-2 text-3xl font-semibold text-foreground">
                        {value}
                    </p>
                </div>
                <span className="rounded-lg bg-primary/10 p-3 text-primary">
                    <Icon className="size-5" aria-hidden="true" />
                </span>
            </div>
            <p className="mt-4 text-sm text-muted-foreground">{description}</p>
        </Link>
    );
}

function HealthMetric({
    label,
    value,
    unit,
}: {
    label: string;
    value: number | string | null;
    unit?: string;
}) {
    return (
        <div className="rounded-lg bg-muted/50 p-3">
            <p className="text-xs text-muted-foreground">{label}</p>
            <p className="mt-1 font-semibold text-foreground">
                {value ?? "—"}
                {value !== null && unit ? ` ${unit}` : ""}
            </p>
        </div>
    );
}

export default function PatientDashboard() {
    const { patientinfo } = usePage<
        AppPageProps & { patientinfo: PatientDashboardInfo }
    >().props;
    const { medications, vitals, pregnancy, cycle } = patientinfo;

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Patient Dashboard" />
            <div className="flex flex-1 flex-col gap-6 p-4 md:p-6">
                <section className="rounded-xl bg-primary p-6 text-primary-foreground md:p-8">
                    <p className="text-sm font-medium text-primary-foreground/80">
                        Patient dashboard
                    </p>
                    <h1 className="mt-2 text-2xl font-semibold md:text-3xl">
                        Welcome back, {patientinfo.name}
                    </h1>
                    <p className="mt-2 max-w-2xl text-sm text-primary-foreground/80">
                        Your appointments, medications, and health information
                        at a glance.
                    </p>
                </section>

                <section className="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                    <SummaryCard
                        title="Appointments"
                        value={patientinfo.totalappoinments}
                        description={`${patientinfo.upcomingAppointments} upcoming`}
                        icon={CalendarDays}
                        href={route("appointments.index")}
                    />
                    <SummaryCard
                        title="Pending appointments"
                        value={patientinfo.newappointments}
                        description="Awaiting confirmation"
                        icon={Clock3}
                        href={route("appointments.index")}
                    />
                    <SummaryCard
                        title="Prescriptions"
                        value={patientinfo.totalprescriptions}
                        description={`${patientinfo.activePrescriptions} active`}
                        icon={Pill}
                        href={route("prescriptions.index")}
                    />
                </section>

                <section className="grid gap-6 xl:grid-cols-2">
                    <article className="rounded-xl border border-sidebar-border/70 bg-card p-5 dark:border-sidebar-border">
                        <div className="flex items-center justify-between gap-4">
                            <div>
                                <h2 className="text-lg font-semibold text-foreground">
                                    Medication overview
                                </h2>
                                <p className="mt-1 text-sm text-muted-foreground">
                                    Today’s scheduled doses and recent adherence
                                </p>
                            </div>
                            <Pill
                                className="size-5 text-primary"
                                aria-hidden="true"
                            />
                        </div>
                        <div className="mt-5 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5">
                            <HealthMetric
                                label="Active medicines"
                                value={medications.active}
                            />
                            <HealthMetric
                                label="Due today"
                                value={medications.today.total}
                            />
                            <HealthMetric
                                label="Taken today"
                                value={medications.today.taken}
                            />
                            <HealthMetric
                                label="Upcoming today"
                                value={medications.today.upcoming}
                            />
                            <HealthMetric
                                label="Missed today"
                                value={medications.today.missed}
                            />
                        </div>
                        <div className="mt-5 flex items-center gap-3 rounded-lg bg-muted/50 p-4">
                            <CheckCircle2
                                className="size-5 shrink-0 text-emerald-600 dark:text-emerald-400"
                                aria-hidden="true"
                            />
                            <p className="text-sm text-foreground">
                                30-day adherence:{" "}
                                <span className="font-semibold">
                                    {medications.adherence.percentage === null
                                        ? "No dose history"
                                        : `${medications.adherence.percentage}%`}
                                </span>
                                {medications.adherence.total > 0 && (
                                    <span className="text-muted-foreground">
                                        {" "}
                                        ({medications.adherence.taken}/
                                        {medications.adherence.total} doses)
                                    </span>
                                )}
                            </p>
                        </div>
                    </article>

                    <article className="rounded-xl border border-sidebar-border/70 bg-card p-5 dark:border-sidebar-border">
                        <div className="flex items-center justify-between gap-4">
                            <div>
                                <h2 className="text-lg font-semibold text-foreground">
                                    Latest vitals
                                </h2>
                                <p className="mt-1 text-sm text-muted-foreground">
                                    {vitals.recordedAt
                                        ? `Recorded ${vitals.recordedAt}`
                                        : "Your latest recorded health metrics"}
                                </p>
                            </div>
                            <HeartPulse
                                className="size-5 text-primary"
                                aria-hidden="true"
                            />
                        </div>
                        {vitals.recordedAt ? (
                            <div className="mt-5 grid grid-cols-2 gap-3 sm:grid-cols-3">
                                <HealthMetric
                                    label="Blood pressure"
                                    value={vitals.bloodPressure}
                                    unit="mmHg"
                                />
                                <HealthMetric
                                    label="Heart rate"
                                    value={vitals.heartRate}
                                    unit="bpm"
                                />
                                <HealthMetric
                                    label="Oxygen"
                                    value={vitals.oxygenSaturation}
                                    unit="%"
                                />
                                <HealthMetric
                                    label="Temperature"
                                    value={vitals.temperature}
                                    unit="°C"
                                />
                                <HealthMetric
                                    label="Weight"
                                    value={vitals.weight}
                                    unit="kg"
                                />
                                <HealthMetric label="BMI" value={vitals.bmi} />
                            </div>
                        ) : (
                            <div className="mt-5 rounded-lg bg-muted/50 p-4 text-sm text-muted-foreground">
                                No vital readings have been recorded yet.
                            </div>
                        )}
                    </article>
                </section>

                <section className="grid gap-6 lg:grid-cols-2">
                    <article className="rounded-xl border border-sidebar-border/70 bg-card p-5 dark:border-sidebar-border">
                        <div className="flex items-center gap-3">
                            <span className="rounded-lg bg-pink-100 p-2 text-pink-700 dark:bg-pink-950 dark:text-pink-300">
                                <Baby className="size-5" aria-hidden="true" />
                            </span>
                            <div>
                                <h2 className="text-lg font-semibold text-foreground">
                                    Pregnancy tracking
                                </h2>
                                <p className="text-sm text-muted-foreground">
                                    Your pregnancy profile summary
                                </p>
                            </div>
                        </div>
                        <div className="mt-5">
                            {pregnancy.status === "active" ? (
                                <>
                                    <p className="text-2xl font-semibold text-foreground">
                                        Week {pregnancy.currentWeek}
                                    </p>
                                    <p className="mt-1 text-sm text-muted-foreground">
                                        Estimated due date:{" "}
                                        {pregnancy.estimatedDueDate}
                                    </p>
                                </>
                            ) : (
                                <p className="text-sm text-muted-foreground">
                                    {pregnancy.status === "inactive"
                                        ? "Pregnancy tracking is inactive."
                                        : pregnancy.status === "needs_review"
                                          ? "Review your pregnancy dates to see an accurate summary."
                                          : "No pregnancy tracking profile has been set up."}
                                </p>
                            )}
                        </div>
                    </article>

                    <article className="rounded-xl border border-sidebar-border/70 bg-card p-5 dark:border-sidebar-border">
                        <div className="flex items-center gap-3">
                            <span className="rounded-lg bg-violet-100 p-2 text-violet-700 dark:bg-violet-950 dark:text-violet-300">
                                <Activity
                                    className="size-5"
                                    aria-hidden="true"
                                />
                            </span>
                            <div>
                                <h2 className="text-lg font-semibold text-foreground">
                                    Menstrual cycle
                                </h2>
                                <p className="text-sm text-muted-foreground">
                                    Cycle phase and next-period estimate
                                </p>
                            </div>
                        </div>
                        <div className="mt-5">
                            {cycle.status === "active" ? (
                                <>
                                    <p className="text-2xl font-semibold text-foreground">
                                        {cycle.phase}
                                    </p>
                                    <p className="mt-1 text-sm text-muted-foreground">
                                        {cycle.currentCycleDay
                                            ? `Cycle day ${cycle.currentCycleDay}`
                                            : "Cycle day unavailable"}
                                        {cycle.nextPeriod
                                            ? ` · Next period: ${cycle.nextPeriod}`
                                            : ""}
                                    </p>
                                </>
                            ) : (
                                <p className="text-sm text-muted-foreground">
                                    {cycle.status === "inactive"
                                        ? "Menstrual cycle tracking is inactive."
                                        : "No menstrual cycle profile has been set up."}
                                </p>
                            )}
                        </div>
                    </article>
                </section>
            </div>
        </AppLayout>
    );
}
