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
    Calendar,
    ClipboardList,
    Droplets,
    HeartPulse,
    Mail,
    Phone,
    Stethoscope,
    UserRound,
} from "lucide-react";

interface VitalRecord {
    id: number;
    blood_pressure: string | null;
    heart_rate: number | string | null;
    temperature: number | string | null;
    respiratory_rate: number | string | null;
    oxygen_saturation: number | string | null;
    weight: number | string | null;
    height: number | string | null;
    bmi: number | string | null;
    notes: string | null;
    created_at: string;
}

interface PrescriptionMedication {
    id: number;
    strength: string | null;
    dosage: string | null;
    duration: string | null;
    meal_time: string | null;
    advice: string | null;
    medicine: {
        brand_name: string | null;
        generic_name: string | null;
    } | null;
}

interface PrescriptionRecord {
    id: number;
    prescription_number: string;
    diagnosis: string | null;
    status: string;
    followup_date: string | null;
    created_at: string;
    doctor: {
        name: string;
    } | null;
    medications: PrescriptionMedication[];
}

interface PatientRecord {
    id: number;
    patient_number: string;
    name: string;
    date_of_birth: string | null;
    gender: string | null;
    marital_status: string | null;
    phone: string;
    email: string | null;
    address: string | null;
    city: string | null;
    state: string | null;
    postal_code: string | null;
    country: string | null;
    blood_group: string | null;
    relationship: string | null;
    emergency_contact_name: string | null;
    emergency_contact_phone: string | null;
    emergency_contact_relationship: string | null;
    user_id: number | null;
    profile_image_url: string | null;
    created_at: string;
    vitals: VitalRecord[];
    prescriptions: PrescriptionRecord[];
}

interface ShowProps {
    patient: PatientRecord;
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: "Patients", href: "/admin/patients" },
    { title: "Patient Details", href: "" },
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

function InfoRow({
    label,
    value,
}: {
    label: string;
    value: string | null | undefined;
}) {
    if (!value) {
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

function PrescriptionStatus({ status }: { status: string }) {
    const normalizedStatus = status.toLowerCase();
    const variant =
        normalizedStatus === "cancelled"
            ? "destructive"
            : normalizedStatus === "draft"
              ? "secondary"
              : "outline";

    return (
        <Badge variant={variant} className="capitalize">
            {normalizedStatus}
        </Badge>
    );
}

function formatMetric(value: number | string | null, unit: string): string {
    return value === null || value === "" ? "Not recorded" : `${value}${unit}`;
}

export default function Show({ patient }: ShowProps) {
    const vitals = patient.vitals ?? [];
    const prescriptions = patient.prescriptions ?? [];
    const latestVital = vitals[0];
    const patientAddress = [
        patient.address,
        patient.city,
        patient.state,
        patient.postal_code,
        patient.country,
    ]
        .filter(Boolean)
        .join(", ");

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Patient - ${patient.name}`} />

            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4">
                <AdminPageHeader
                    breadcrumbs={breadcrumbs}
                    description="Patient profile, vital signs, and prescription history"
                    actions={
                        <Button
                            variant="outline"
                            size="sm"
                            onClick={() =>
                                router.visit(route("patients.edit", patient.id))
                            }
                            className="border-white/30 bg-white/10 text-white hover:bg-white/20 hover:text-white"
                        >
                            Edit Patient
                        </Button>
                    }
                />

                <Card>
                    <CardContent className="pt-6">
                        <div className="flex flex-col gap-5 md:flex-row md:items-center md:justify-between">
                            <div className="flex items-center gap-4">
                                <Avatar className="size-20 border-2 border-white shadow-md">
                                    <AvatarImage
                                        src={
                                            patient.profile_image_url ||
                                            undefined
                                        }
                                        alt={patient.name}
                                    />
                                    <AvatarFallback className="bg-primary text-xl font-bold text-primary-foreground">
                                        {getInitials(patient.name)}
                                    </AvatarFallback>
                                </Avatar>
                                <div className="space-y-2">
                                    <div className="flex flex-wrap items-center gap-2">
                                        <h1 className="text-2xl font-bold">
                                            {patient.name}
                                        </h1>
                                        <Badge variant="secondary">
                                            {patient.patient_number}
                                        </Badge>
                                    </div>
                                    <div className="flex flex-wrap gap-x-4 gap-y-2 text-sm text-muted-foreground">
                                        <span className="flex items-center gap-1">
                                            <Phone className="size-4" />
                                            {patient.phone}
                                        </span>
                                        {patient.email && (
                                            <span className="flex items-center gap-1">
                                                <Mail className="size-4" />
                                                {patient.email}
                                            </span>
                                        )}
                                        <span className="flex items-center gap-1">
                                            <Calendar className="size-4" />
                                            Registered{" "}
                                            {formatDate(patient.created_at)}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div className="grid grid-cols-3 gap-4 border-t pt-4 text-center md:border-t-0 md:border-l md:pt-0 md:pl-6">
                                <div>
                                    <p className="text-2xl font-bold">
                                        {prescriptions.length}
                                    </p>
                                    <p className="text-xs text-muted-foreground">
                                        Prescriptions
                                    </p>
                                </div>
                                <div>
                                    <p className="text-2xl font-bold">
                                        {vitals.length}
                                    </p>
                                    <p className="text-xs text-muted-foreground">
                                        Vital records
                                    </p>
                                </div>
                                <div>
                                    <p className="text-2xl font-bold">
                                        {patient.blood_group || "—"}
                                    </p>
                                    <p className="text-xs text-muted-foreground">
                                        Blood group
                                    </p>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <div className="grid gap-6 lg:grid-cols-2">
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2 text-lg">
                                <UserRound className="size-5" />
                                Patient information
                            </CardTitle>
                            <CardDescription>
                                Demographic and contact details
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="grid gap-5 sm:grid-cols-2">
                            <InfoRow
                                label="Date of birth"
                                value={formatDate(patient.date_of_birth)}
                            />
                            <InfoRow label="Gender" value={patient.gender} />
                            <InfoRow
                                label="Marital status"
                                value={patient.marital_status}
                            />
                            <InfoRow
                                label="Relationship"
                                value={patient.relationship}
                            />
                            <InfoRow label="Address" value={patientAddress} />
                            <InfoRow
                                label="Portal account"
                                value={
                                    patient.user_id ? "Linked" : "Not linked"
                                }
                            />
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2 text-lg">
                                <Phone className="size-5" />
                                Emergency contact
                            </CardTitle>
                            <CardDescription>
                                Contact details to use in an emergency
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="grid gap-5 sm:grid-cols-2">
                            <InfoRow
                                label="Name"
                                value={patient.emergency_contact_name}
                            />
                            <InfoRow
                                label="Relationship"
                                value={patient.emergency_contact_relationship}
                            />
                            <InfoRow
                                label="Phone"
                                value={patient.emergency_contact_phone}
                            />
                            {!patient.emergency_contact_name &&
                                !patient.emergency_contact_phone && (
                                    <p className="text-sm text-muted-foreground sm:col-span-2">
                                        No emergency contact recorded.
                                    </p>
                                )}
                        </CardContent>
                    </Card>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2 text-lg">
                            <HeartPulse className="size-5" />
                            Vital signs
                        </CardTitle>
                        <CardDescription>
                            {latestVital
                                ? `Most recent record: ${formatDateTime(latestVital.created_at)}`
                                : "No vital signs have been recorded."}
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        {vitals.length > 0 ? (
                            <div className="space-y-4">
                                {vitals.map((vital) => {
                                    const metrics = [
                                        {
                                            label: "Blood pressure",
                                            value: formatMetric(
                                                vital.blood_pressure,
                                                "",
                                            ),
                                        },
                                        {
                                            label: "Heart rate",
                                            value: formatMetric(
                                                vital.heart_rate,
                                                " bpm",
                                            ),
                                        },
                                        {
                                            label: "Temperature",
                                            value: formatMetric(
                                                vital.temperature,
                                                " °C",
                                            ),
                                        },
                                        {
                                            label: "Respiratory rate",
                                            value: formatMetric(
                                                vital.respiratory_rate,
                                                " /min",
                                            ),
                                        },
                                        {
                                            label: "Oxygen saturation",
                                            value: formatMetric(
                                                vital.oxygen_saturation,
                                                "%",
                                            ),
                                        },
                                        {
                                            label: "Weight",
                                            value: formatMetric(
                                                vital.weight,
                                                " kg",
                                            ),
                                        },
                                        {
                                            label: "Height",
                                            value: formatMetric(
                                                vital.height,
                                                " cm",
                                            ),
                                        },
                                        {
                                            label: "BMI",
                                            value: formatMetric(vital.bmi, ""),
                                        },
                                    ];

                                    return (
                                        <div
                                            key={vital.id}
                                            className="rounded-lg border bg-muted/30 p-4"
                                        >
                                            <div className="mb-3 flex items-center justify-between gap-3">
                                                <p className="flex items-center gap-2 font-medium">
                                                    <Activity className="size-4 text-muted-foreground" />
                                                    Vitals record
                                                </p>
                                                <span className="text-xs text-muted-foreground">
                                                    {formatDateTime(
                                                        vital.created_at,
                                                    )}
                                                </span>
                                            </div>
                                            <div className="grid grid-cols-2 gap-3 sm:grid-cols-4">
                                                {metrics.map((metric) => (
                                                    <div key={metric.label}>
                                                        <p className="text-xs text-muted-foreground">
                                                            {metric.label}
                                                        </p>
                                                        <p className="text-sm font-medium">
                                                            {metric.value}
                                                        </p>
                                                    </div>
                                                ))}
                                            </div>
                                            {vital.notes && (
                                                <p className="mt-3 border-t pt-3 text-sm text-muted-foreground">
                                                    {vital.notes}
                                                </p>
                                            )}
                                        </div>
                                    );
                                })}
                            </div>
                        ) : (
                            <p className="py-8 text-center text-sm text-muted-foreground">
                                No vital signs recorded.
                            </p>
                        )}
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2 text-lg">
                            <ClipboardList className="size-5" />
                            Prescription history
                        </CardTitle>
                        <CardDescription>
                            {prescriptions.length} prescription(s)
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        {prescriptions.length > 0 ? (
                            <div className="space-y-4">
                                {prescriptions.map((prescription) => (
                                    <div
                                        key={prescription.id}
                                        className="rounded-lg border p-4"
                                    >
                                        <div className="flex flex-wrap items-start justify-between gap-3">
                                            <div>
                                                <p className="font-semibold">
                                                    {
                                                        prescription.prescription_number
                                                    }
                                                </p>
                                                <p className="mt-1 text-sm text-muted-foreground">
                                                    {prescription.diagnosis ||
                                                        "No diagnosis recorded"}
                                                </p>
                                            </div>
                                            <PrescriptionStatus
                                                status={prescription.status}
                                            />
                                        </div>
                                        <div className="mt-3 flex flex-wrap gap-x-5 gap-y-2 text-xs text-muted-foreground">
                                            <span>
                                                Issued:{" "}
                                                {formatDateTime(
                                                    prescription.created_at,
                                                )}
                                            </span>
                                            {prescription.doctor && (
                                                <span>
                                                    Doctor:{" "}
                                                    {prescription.doctor.name}
                                                </span>
                                            )}
                                            <span>
                                                Follow-up:{" "}
                                                {formatDate(
                                                    prescription.followup_date,
                                                )}
                                            </span>
                                        </div>
                                        {prescription.medications.length >
                                            0 && (
                                            <div className="mt-4 space-y-2 border-t pt-3">
                                                {prescription.medications.map(
                                                    (medication) => (
                                                        <div
                                                            key={medication.id}
                                                            className="flex items-start gap-2 text-sm"
                                                        >
                                                            <Droplets className="mt-0.5 size-4 shrink-0 text-muted-foreground" />
                                                            <div>
                                                                <p className="font-medium">
                                                                    {medication
                                                                        .medicine
                                                                        ?.brand_name ||
                                                                        medication
                                                                            .medicine
                                                                            ?.generic_name ||
                                                                        "Medicine"}
                                                                    {medication.strength
                                                                        ? ` · ${medication.strength}`
                                                                        : ""}
                                                                </p>
                                                                <p className="text-xs text-muted-foreground">
                                                                    {[
                                                                        medication.dosage,
                                                                        medication.duration,
                                                                        medication.meal_time,
                                                                        medication.advice,
                                                                    ]
                                                                        .filter(
                                                                            Boolean,
                                                                        )
                                                                        .join(
                                                                            " · ",
                                                                        ) ||
                                                                        "No instructions recorded"}
                                                                </p>
                                                            </div>
                                                        </div>
                                                    ),
                                                )}
                                            </div>
                                        )}
                                    </div>
                                ))}
                            </div>
                        ) : (
                            <div className="flex flex-col items-center gap-2 py-8 text-center text-sm text-muted-foreground">
                                <Stethoscope className="size-5" />
                                <p>No prescriptions found for this patient.</p>
                            </div>
                        )}
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
