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
    Award,
    BriefcaseBusiness,
    Calendar,
    GraduationCap,
    Mail,
    Phone,
    Stethoscope,
    UserRound,
} from "lucide-react";

interface DoctorUser {
    name: string;
    email: string;
    mobile: string | null;
    profile_image_url: string | null;
    specialties: DoctorSpecialty[];
}

interface DoctorSpecialty {
    id: number;
    name: string;
}

interface DoctorEducation {
    id: number;
    degree: string;
    institute: string;
    year: string;
    country: string;
}

interface DoctorExperience {
    id: number;
    hospital_name: string;
    position: string;
    from_year: string;
    to_year: string;
    description: string | null;
}

interface DoctorRecord {
    id: number;
    uuid: string;
    phone: string | null;
    registration_no: string | null;
    gender: string | null;
    dob: string | null;
    specialization: string | null;
    working_institute: string | null;
    designation: string | null;
    qualification: string | null;
    experience_years: number | null;
    bio: string | null;
    active: boolean;
    featured: boolean;
    created_at: string;
    user: DoctorUser;
    educations: DoctorEducation[];
    experiences: DoctorExperience[];
}

interface ShowProps {
    doctor: DoctorRecord;
}

function formatDate(date: string | null): string {
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
    value: string | number | null;
}) {
    return (
        <div className="space-y-1">
            <p className="text-xs font-medium tracking-wide text-muted-foreground uppercase">
                {label}
            </p>
            <p className="text-sm break-words">{value || "Not recorded"}</p>
        </div>
    );
}

export default function Show({ doctor }: ShowProps) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: "Doctors", href: route("doctors.index") },
        { title: "Doctor Details", href: "" },
    ];
    const doctorPhone = doctor.phone || doctor.user.mobile;
    const specialties = doctor.user.specialties ?? [];
    const educations = doctor.educations ?? [];
    const experiences = doctor.experiences ?? [];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Doctor - ${doctor.user.name}`} />

            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4">
                <AdminPageHeader
                    breadcrumbs={breadcrumbs}
                    description="Doctor profile, qualifications, and professional experience"
                    actions={
                        <Button
                            variant="outline"
                            size="sm"
                            onClick={() =>
                                router.visit(route("doctors.edit", doctor.uuid))
                            }
                            className="border-white/30 bg-white/10 text-white hover:bg-white/20 hover:text-white"
                        >
                            Edit Doctor
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
                                            doctor.user.profile_image_url ||
                                            undefined
                                        }
                                        alt={doctor.user.name}
                                    />
                                    <AvatarFallback className="bg-primary text-xl font-bold text-primary-foreground">
                                        {getInitials(doctor.user.name)}
                                    </AvatarFallback>
                                </Avatar>
                                <div className="space-y-2">
                                    <div className="flex flex-wrap items-center gap-2">
                                        <h1 className="text-2xl font-bold">
                                            {doctor.user.name}
                                        </h1>
                                        <Badge
                                            variant={
                                                doctor.active
                                                    ? "secondary"
                                                    : "destructive"
                                            }
                                        >
                                            {doctor.active
                                                ? "Active"
                                                : "Inactive"}
                                        </Badge>
                                        {doctor.featured && (
                                            <Badge variant="outline">
                                                Featured
                                            </Badge>
                                        )}
                                    </div>
                                    <div className="flex flex-wrap gap-x-4 gap-y-2 text-sm text-muted-foreground">
                                        {doctorPhone && (
                                            <span className="flex items-center gap-1">
                                                <Phone className="size-4" />
                                                {doctorPhone}
                                            </span>
                                        )}
                                        <span className="flex items-center gap-1">
                                            <Mail className="size-4" />
                                            {doctor.user.email}
                                        </span>
                                        <span className="flex items-center gap-1">
                                            <Calendar className="size-4" />
                                            Registered{" "}
                                            {formatDate(doctor.created_at)}
                                        </span>
                                    </div>
                                    <div className="flex flex-wrap gap-2">
                                        {specialties.map((specialty) => (
                                            <Badge
                                                key={specialty.id}
                                                variant="outline"
                                            >
                                                {specialty.name}
                                            </Badge>
                                        ))}
                                        {specialties.length === 0 &&
                                            doctor.specialization && (
                                                <Badge variant="outline">
                                                    {doctor.specialization}
                                                </Badge>
                                            )}
                                    </div>
                                </div>
                            </div>
                            <div className="grid grid-cols-3 gap-4 border-t pt-4 text-center md:border-t-0 md:border-l md:pt-0 md:pl-6">
                                <div>
                                    <p className="text-2xl font-bold">
                                        {doctor.experience_years ?? "—"}
                                    </p>
                                    <p className="text-xs text-muted-foreground">
                                        Years experience
                                    </p>
                                </div>
                                <div>
                                    <p className="text-2xl font-bold">
                                        {educations.length}
                                    </p>
                                    <p className="text-xs text-muted-foreground">
                                        Qualifications
                                    </p>
                                </div>
                                <div>
                                    <p className="text-2xl font-bold">
                                        {experiences.length}
                                    </p>
                                    <p className="text-xs text-muted-foreground">
                                        Positions
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
                                Personal information
                            </CardTitle>
                            <CardDescription>
                                Contact and registration details
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="grid gap-5 sm:grid-cols-2">
                            <InfoRow label="Email" value={doctor.user.email} />
                            <InfoRow label="Phone" value={doctorPhone} />
                            <InfoRow label="Gender" value={doctor.gender} />
                            <InfoRow
                                label="Date of birth"
                                value={formatDate(doctor.dob)}
                            />
                            <InfoRow
                                label="Registration number"
                                value={doctor.registration_no}
                            />
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2 text-lg">
                                <Stethoscope className="size-5" />
                                Professional profile
                            </CardTitle>
                            <CardDescription>
                                Current practice and qualifications
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="grid gap-5 sm:grid-cols-2">
                            <InfoRow
                                label="Specialization"
                                value={doctor.specialization}
                            />
                            <InfoRow
                                label="Designation"
                                value={doctor.designation}
                            />
                            <InfoRow
                                label="Qualification"
                                value={doctor.qualification}
                            />
                            <InfoRow
                                label="Working institute"
                                value={doctor.working_institute}
                            />
                            <InfoRow
                                label="Years of experience"
                                value={
                                    doctor.experience_years === null
                                        ? null
                                        : `${doctor.experience_years} years`
                                }
                            />
                            {doctor.bio && (
                                <div className="space-y-1 sm:col-span-2">
                                    <p className="text-xs font-medium tracking-wide text-muted-foreground uppercase">
                                        Biography
                                    </p>
                                    <p className="text-sm whitespace-pre-line">
                                        {doctor.bio}
                                    </p>
                                </div>
                            )}
                        </CardContent>
                    </Card>
                </div>

                <div className="grid gap-6 lg:grid-cols-2">
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2 text-lg">
                                <GraduationCap className="size-5" />
                                Education
                            </CardTitle>
                            <CardDescription>
                                {educations.length} qualification(s)
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            {educations.length > 0 ? (
                                <div className="space-y-4">
                                    {educations.map((education) => (
                                        <div
                                            key={education.id}
                                            className="rounded-lg border p-4"
                                        >
                                            <p className="font-semibold">
                                                {education.degree}
                                            </p>
                                            <p className="mt-1 text-sm text-muted-foreground">
                                                {education.institute}
                                            </p>
                                            <p className="mt-2 text-xs text-muted-foreground">
                                                {education.year} ·{" "}
                                                {education.country}
                                            </p>
                                        </div>
                                    ))}
                                </div>
                            ) : (
                                <p className="py-8 text-center text-sm text-muted-foreground">
                                    No education records available.
                                </p>
                            )}
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2 text-lg">
                                <BriefcaseBusiness className="size-5" />
                                Work experience
                            </CardTitle>
                            <CardDescription>
                                {experiences.length} position(s)
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            {experiences.length > 0 ? (
                                <div className="space-y-4">
                                    {experiences.map((experience) => (
                                        <div
                                            key={experience.id}
                                            className="rounded-lg border p-4"
                                        >
                                            <div className="flex items-start gap-3">
                                                <Award className="mt-0.5 size-4 shrink-0 text-muted-foreground" />
                                                <div>
                                                    <p className="font-semibold">
                                                        {experience.position}
                                                    </p>
                                                    <p className="mt-1 text-sm text-muted-foreground">
                                                        {
                                                            experience.hospital_name
                                                        }
                                                    </p>
                                                    <p className="mt-2 text-xs text-muted-foreground">
                                                        {experience.from_year} -{" "}
                                                        {experience.to_year}
                                                    </p>
                                                    {experience.description && (
                                                        <p className="mt-3 text-sm whitespace-pre-line">
                                                            {
                                                                experience.description
                                                            }
                                                        </p>
                                                    )}
                                                </div>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            ) : (
                                <p className="py-8 text-center text-sm text-muted-foreground">
                                    No work experience records available.
                                </p>
                            )}
                        </CardContent>
                    </Card>
                </div>
            </div>
        </AppLayout>
    );
}
