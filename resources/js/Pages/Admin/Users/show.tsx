import { AdminPageHeader } from "@/Components/AdminPageHeader";
import { Avatar, AvatarFallback, AvatarImage } from "@/Components/ui/avatar";
import { Badge } from "@/Components/ui/badge";
import { Button } from "@/Components/ui/button";
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from "@/Components/ui/card";
import AppLayout from "@/Layouts/app-layout";
import { type BreadcrumbItem } from "@/types";
import { Head, router } from "@inertiajs/react";
import {
    Activity,
    CalendarDays,
    ClipboardList,
    Download,
    FileSpreadsheet,
    FileText,
    Mail,
    Phone,
    Stethoscope,
    UserRound,
} from "lucide-react";

interface Role {
    id: number;
    name: string;
}

interface DoctorProfile {
    registration_no: string | null;
    specialization: string | null;
    designation: string | null;
    qualification: string | null;
    experience_years: number | null;
    working_institute: string | null;
    bio: string | null;
}

interface UserData {
    id: number;
    name: string;
    email: string;
    mobile: string | null;
    status: string;
    email_verified_at: string | null;
    avatar: string | null;
    created_at: string;
    roles: Role[];
    doctor: DoctorProfile | null;
}

interface PrescriptionSummary {
    patients: number;
    prescriptions: number;
    new_visits: number;
    follow_up_visits: number;
}

interface PatientAdherence {
    patient_id: number;
    patient_name: string;
    patient_number: string;
    eligible_doses: number;
    taken: number;
    missed: number;
    percentage: number;
}

interface PrescriptionLog {
    id: number;
    prescription_number: string;
    diagnosis: string | null;
    is_followup: boolean;
    status: string;
    created_at: string;
    medications: {
        id: number;
        strength: string | null;
        medicine: {
            brand_name: string | null;
            generic_name: string | null;
        } | null;
    }[];
    patient: {
        name: string;
        patient_number: string;
    } | null;
}

interface DoctorAnalytics {
    daily: PrescriptionSummary;
    monthly: PrescriptionSummary;
    adherence: {
        period_days: number;
        eligible_doses: number;
        taken: number;
        missed: number;
        skipped: number;
        percentage: number | null;
    };
    patient_adherence: PatientAdherence[];
    recent_prescriptions: PrescriptionLog[];
}

interface ShowProps {
    user: UserData;
    doctorAnalytics: DoctorAnalytics | null;
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: "Users", href: "/admin/users" },
    { title: "User Details", href: "" },
];

function formatDate(date: string | null | undefined): string {
    if (!date) {
        return "Not recorded";
    }

    const parsedDate = new Date(date);

    return Number.isNaN(parsedDate.getTime())
        ? date
        : parsedDate.toLocaleDateString("en-GB", {
              year: "numeric",
              month: "short",
              day: "numeric",
          });
}

function formatDateTime(date: string): string {
    const parsedDate = new Date(date);

    return Number.isNaN(parsedDate.getTime())
        ? date
        : parsedDate.toLocaleString("en-GB", {
              year: "numeric",
              month: "short",
              day: "numeric",
              hour: "2-digit",
              minute: "2-digit",
          });
}

function getInitials(name: string): string {
    return name
        .trim()
        .split(/\s+/)
        .map((part) => part[0])
        .join("")
        .toUpperCase()
        .slice(0, 2);
}

function MetricCard({
    label,
    value,
    description,
    icon: Icon,
}: {
    label: string;
    value: number | string;
    description: string;
    icon: typeof Activity;
}) {
    return (
        <Card>
            <CardContent className="flex items-start justify-between gap-4 pt-6">
                <div className="space-y-1">
                    <p className="text-sm text-muted-foreground">{label}</p>
                    <p className="text-3xl font-semibold">{value}</p>
                    <p className="text-xs text-muted-foreground">
                        {description}
                    </p>
                </div>
                <span className="rounded-lg bg-primary/10 p-2 text-primary">
                    <Icon className="size-5" />
                </span>
            </CardContent>
        </Card>
    );
}

function ExportLink({
    userId,
    format,
    children,
}: {
    userId: number;
    format: "csv" | "pdf";
    children: React.ReactNode;
}) {
    return (
        <a
            href={route("users.analytics.export", {
                id: userId,
                format,
            })}
            className="inline-flex h-9 items-center justify-center gap-2 rounded-md border border-input bg-background px-3 text-sm font-medium shadow-sm transition-colors hover:bg-accent hover:text-accent-foreground"
        >
            {children}
        </a>
    );
}

export default function Show({ user, doctorAnalytics }: ShowProps) {
    const isDoctor = user.roles.some(
        (role) => role.name.toLowerCase() === "doctor",
    );
    const exportAnalytics = isDoctor && doctorAnalytics !== null;

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`${isDoctor ? "Doctor" : "User"} - ${user.name}`} />

            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4">
                <AdminPageHeader
                    breadcrumbs={breadcrumbs}
                    description={
                        isDoctor
                            ? "Doctor profile, prescription statistics, and patient adherence"
                            : "User profile and account details"
                    }
                    actions={
                        <Button
                            variant="outline"
                            size="sm"
                            onClick={() =>
                                router.visit(route("users.edit", user.id))
                            }
                            className="border-white/30 bg-white/10 text-white hover:bg-white/20 hover:text-white"
                        >
                            Edit User
                        </Button>
                    }
                />

                <Card>
                    <CardContent className="pt-6">
                        <div className="flex flex-col gap-5 md:flex-row md:items-center md:justify-between">
                            <div className="flex items-center gap-4">
                                <Avatar className="size-20 border-2 border-white shadow-md">
                                    <AvatarImage
                                        src={user.avatar || undefined}
                                        alt={user.name}
                                    />
                                    <AvatarFallback className="bg-primary text-xl font-bold text-primary-foreground">
                                        {getInitials(user.name)}
                                    </AvatarFallback>
                                </Avatar>
                                <div className="space-y-2">
                                    <div className="flex flex-wrap items-center gap-2">
                                        <h1 className="text-2xl font-bold">
                                            {user.name}
                                        </h1>
                                        <Badge variant="secondary">
                                            {user.status}
                                        </Badge>
                                        {user.email_verified_at && (
                                            <Badge
                                                variant="outline"
                                                className="border-green-200 text-green-700"
                                            >
                                                Verified
                                            </Badge>
                                        )}
                                    </div>
                                    <div className="flex flex-wrap gap-x-4 gap-y-2 text-sm text-muted-foreground">
                                        <span className="flex items-center gap-1">
                                            <Mail className="size-4" />
                                            {user.email}
                                        </span>
                                        {user.mobile && (
                                            <span className="flex items-center gap-1">
                                                <Phone className="size-4" />
                                                {user.mobile}
                                            </span>
                                        )}
                                        <span className="flex items-center gap-1">
                                            <CalendarDays className="size-4" />
                                            Joined {formatDate(user.created_at)}
                                        </span>
                                    </div>
                                    <div className="flex flex-wrap gap-2">
                                        {user.roles.map((role) => (
                                            <Badge
                                                key={role.id}
                                                variant="outline"
                                                className="capitalize"
                                            >
                                                {role.name}
                                            </Badge>
                                        ))}
                                    </div>
                                </div>
                            </div>
                            {exportAnalytics && (
                                <div className="flex flex-wrap gap-2">
                                    <ExportLink userId={user.id} format="csv">
                                        <FileSpreadsheet className="size-4" />
                                        Export CSV
                                    </ExportLink>
                                    <ExportLink userId={user.id} format="pdf">
                                        <FileText className="size-4" />
                                        Export PDF
                                    </ExportLink>
                                </div>
                            )}
                        </div>
                    </CardContent>
                </Card>

                {isDoctor && user.doctor && (
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2 text-lg">
                                <Stethoscope className="size-5" />
                                Doctor profile
                            </CardTitle>
                            <CardDescription>
                                Professional and practice information
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                            <ProfileValue
                                label="Specialization"
                                value={user.doctor.specialization}
                            />
                            <ProfileValue
                                label="Designation"
                                value={user.doctor.designation}
                            />
                            <ProfileValue
                                label="Qualification"
                                value={user.doctor.qualification}
                            />
                            <ProfileValue
                                label="Registration number"
                                value={user.doctor.registration_no}
                            />
                            <ProfileValue
                                label="Experience"
                                value={
                                    user.doctor.experience_years === null
                                        ? null
                                        : `${user.doctor.experience_years} years`
                                }
                            />
                            <ProfileValue
                                label="Institute"
                                value={user.doctor.working_institute}
                            />
                            {user.doctor.bio && (
                                <div className="sm:col-span-2 lg:col-span-3">
                                    <ProfileValue
                                        label="Professional bio"
                                        value={user.doctor.bio}
                                    />
                                </div>
                            )}
                        </CardContent>
                    </Card>
                )}

                {exportAnalytics && (
                    <>
                        <section className="space-y-3">
                            <div>
                                <h2 className="text-xl font-semibold">
                                    Prescription statistics
                                </h2>
                                <p className="text-sm text-muted-foreground">
                                    Distinct patients and issued prescription
                                    visits
                                </p>
                            </div>
                            <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                                <MetricCard
                                    label="Patients today"
                                    value={doctorAnalytics.daily.patients}
                                    description="Unique patients with an issued prescription"
                                    icon={UserRound}
                                />
                                <MetricCard
                                    label="Prescriptions today"
                                    value={doctorAnalytics.daily.prescriptions}
                                    description={`${doctorAnalytics.daily.new_visits} new · ${doctorAnalytics.daily.follow_up_visits} follow-up`}
                                    icon={ClipboardList}
                                />
                                <MetricCard
                                    label="Patients this month"
                                    value={doctorAnalytics.monthly.patients}
                                    description="Unique patients with an issued prescription"
                                    icon={UserRound}
                                />
                                <MetricCard
                                    label="Prescriptions this month"
                                    value={
                                        doctorAnalytics.monthly.prescriptions
                                    }
                                    description={`${doctorAnalytics.monthly.new_visits} new · ${doctorAnalytics.monthly.follow_up_visits} follow-up`}
                                    icon={ClipboardList}
                                />
                            </div>
                        </section>

                        <div className="grid gap-6 lg:grid-cols-5">
                            <Card className="lg:col-span-2">
                                <CardHeader>
                                    <CardTitle className="flex items-center gap-2 text-lg">
                                        <Activity className="size-5" />
                                        Patient adherence
                                    </CardTitle>
                                    <CardDescription>
                                        Aggregated from eligible medication logs
                                        in the last{" "}
                                        {doctorAnalytics.adherence.period_days}{" "}
                                        days
                                    </CardDescription>
                                </CardHeader>
                                <CardContent className="space-y-5">
                                    <div>
                                        <p className="text-4xl font-semibold">
                                            {doctorAnalytics.adherence
                                                .percentage === null
                                                ? "—"
                                                : `${doctorAnalytics.adherence.percentage}%`}
                                        </p>
                                        <p className="text-sm text-muted-foreground">
                                            Compliance rate
                                        </p>
                                    </div>
                                    <div className="grid grid-cols-2 gap-3">
                                        <AdherenceCount
                                            label="Taken"
                                            count={
                                                doctorAnalytics.adherence.taken
                                            }
                                        />
                                        <AdherenceCount
                                            label="Missed"
                                            count={
                                                doctorAnalytics.adherence.missed
                                            }
                                        />
                                        <AdherenceCount
                                            label="Skipped"
                                            count={
                                                doctorAnalytics.adherence
                                                    .skipped
                                            }
                                        />
                                        <AdherenceCount
                                            label="Eligible doses"
                                            count={
                                                doctorAnalytics.adherence
                                                    .eligible_doses
                                            }
                                        />
                                    </div>
                                    <p className="text-xs text-muted-foreground">
                                        Compliance is taken doses divided by
                                        taken plus missed doses. Skipped doses
                                        are excluded.
                                    </p>
                                </CardContent>
                            </Card>

                            <Card className="lg:col-span-3">
                                <CardHeader>
                                    <CardTitle className="text-lg">
                                        Patient compliance overview
                                    </CardTitle>
                                    <CardDescription>
                                        Patients with the most missed doses
                                        during this period
                                    </CardDescription>
                                </CardHeader>
                                <CardContent>
                                    {doctorAnalytics.patient_adherence.length >
                                    0 ? (
                                        <div className="space-y-4">
                                            {doctorAnalytics.patient_adherence.map(
                                                (patient) => (
                                                    <div
                                                        key={patient.patient_id}
                                                        className="space-y-2"
                                                    >
                                                        <div className="flex flex-wrap items-center justify-between gap-2 text-sm">
                                                            <div>
                                                                <p className="font-medium">
                                                                    {
                                                                        patient.patient_name
                                                                    }
                                                                </p>
                                                                <p className="text-xs text-muted-foreground">
                                                                    {
                                                                        patient.patient_number
                                                                    }
                                                                    {" · "}
                                                                    {
                                                                        patient.eligible_doses
                                                                    }{" "}
                                                                    eligible
                                                                    doses
                                                                </p>
                                                            </div>
                                                            <div className="text-right">
                                                                <p className="font-semibold">
                                                                    {
                                                                        patient.percentage
                                                                    }
                                                                    %
                                                                </p>
                                                                <p className="text-xs text-muted-foreground">
                                                                    {
                                                                        patient.missed
                                                                    }{" "}
                                                                    missed
                                                                </p>
                                                            </div>
                                                        </div>
                                                        <div className="h-2 overflow-hidden rounded-full bg-muted">
                                                            <div
                                                                className="h-full rounded-full bg-primary"
                                                                style={{
                                                                    width: `${Math.min(patient.percentage, 100)}%`,
                                                                }}
                                                            />
                                                        </div>
                                                    </div>
                                                ),
                                            )}
                                        </div>
                                    ) : (
                                        <EmptyState message="No eligible adherence records found in the last 30 days." />
                                    )}
                                </CardContent>
                            </Card>
                        </div>

                        <Card>
                            <CardHeader>
                                <CardTitle className="flex items-center gap-2 text-lg">
                                    <ClipboardList className="size-5" />
                                    Recent prescription log
                                </CardTitle>
                                <CardDescription>
                                    Latest prescriptions issued by this doctor
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                {doctorAnalytics.recent_prescriptions.length >
                                0 ? (
                                    <div className="overflow-x-auto">
                                        <table className="w-full min-w-[720px] text-left text-sm">
                                            <thead>
                                                <tr className="border-b text-xs text-muted-foreground">
                                                    <th className="px-3 py-2 font-medium">
                                                        Date
                                                    </th>
                                                    <th className="px-3 py-2 font-medium">
                                                        Patient
                                                    </th>
                                                    <th className="px-3 py-2 font-medium">
                                                        Prescription
                                                    </th>
                                                    <th className="px-3 py-2 font-medium">
                                                        Visit
                                                    </th>
                                                    <th className="px-3 py-2 font-medium">
                                                        Status
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                {doctorAnalytics.recent_prescriptions.map(
                                                    (prescription) => (
                                                        <tr
                                                            key={
                                                                prescription.id
                                                            }
                                                            className="border-b last:border-0"
                                                        >
                                                            <td className="px-3 py-3">
                                                                {formatDateTime(
                                                                    prescription.created_at,
                                                                )}
                                                            </td>
                                                            <td className="px-3 py-3">
                                                                <p className="font-medium">
                                                                    {prescription
                                                                        .patient
                                                                        ?.name ??
                                                                        "Patient unavailable"}
                                                                </p>
                                                                <p className="text-xs text-muted-foreground">
                                                                    {prescription
                                                                        .patient
                                                                        ?.patient_number ??
                                                                        ""}
                                                                </p>
                                                            </td>
                                                            <td className="px-3 py-3">
                                                                <p className="font-medium">
                                                                    {
                                                                        prescription.prescription_number
                                                                    }
                                                                </p>
                                                                <p className="max-w-xs truncate text-xs text-muted-foreground">
                                                                    {prescription.diagnosis ??
                                                                        "No diagnosis"}
                                                                </p>
                                                                {prescription.medications.length >
                                                                    0 && (
                                                                    <p className="max-w-xs truncate text-xs text-muted-foreground">
                                                                        {prescription.medications
                                                                            .map(
                                                                                (
                                                                                    medication,
                                                                                ) =>
                                                                                    `${
                                                                                        medication
                                                                                            .medicine
                                                                                            ?.brand_name ??
                                                                                        medication
                                                                                            .medicine
                                                                                            ?.generic_name ??
                                                                                        "Medicine"
                                                                                    } ${
                                                                                        medication.strength ??
                                                                                        ""
                                                                                    }`,
                                                                            )
                                                                            .join(
                                                                                ", ",
                                                                            )}
                                                                    </p>
                                                                )}
                                                            </td>
                                                            <td className="px-3 py-3">
                                                                <Badge variant="outline">
                                                                    {prescription.is_followup
                                                                        ? "Follow-up"
                                                                        : "New"}
                                                                </Badge>
                                                            </td>
                                                            <td className="px-3 py-3">
                                                                <Badge
                                                                    variant={
                                                                        prescription.status ===
                                                                        "cancelled"
                                                                            ? "destructive"
                                                                            : "secondary"
                                                                    }
                                                                    className="capitalize"
                                                                >
                                                                    {
                                                                        prescription.status
                                                                    }
                                                                </Badge>
                                                            </td>
                                                        </tr>
                                                    ),
                                                )}
                                            </tbody>
                                        </table>
                                    </div>
                                ) : (
                                    <EmptyState message="No prescriptions found for this doctor." />
                                )}
                            </CardContent>
                        </Card>
                    </>
                )}

                {isDoctor && !doctorAnalytics && (
                    <Card>
                        <CardContent className="py-8 text-center text-sm text-muted-foreground">
                            Doctor analytics are currently unavailable.
                        </CardContent>
                    </Card>
                )}
            </div>
        </AppLayout>
    );
}

function ProfileValue({
    label,
    value,
}: {
    label: string;
    value: string | number | null;
}) {
    if (value === null || value === "") {
        return null;
    }

    return (
        <div className="space-y-1">
            <p className="text-xs font-medium tracking-wide text-muted-foreground uppercase">
                {label}
            </p>
            <p className="text-sm break-words">{value}</p>
        </div>
    );
}

function AdherenceCount({ label, count }: { label: string; count: number }) {
    return (
        <div className="rounded-lg border p-3">
            <p className="text-xl font-semibold">{count}</p>
            <p className="text-xs text-muted-foreground">{label}</p>
        </div>
    );
}

function EmptyState({ message }: { message: string }) {
    return (
        <div className="flex flex-col items-center gap-2 py-8 text-center text-sm text-muted-foreground">
            <Download className="size-5" />
            <p>{message}</p>
        </div>
    );
}
