import AppLayout from "@/Layouts/app-layout";
import { type BreadcrumbItem } from "@/types";
import { router, useForm, usePoll } from "@inertiajs/react";
import { Head } from "@inertiajs/react";
import { Activity, Hospital, MapPin, Phone, Siren } from "lucide-react";
import { type FormEvent, useState } from "react";

type BloodGroupOption = {
    value: string;
    label: string;
};

type ComponentOption = {
    value: string;
    label: string;
    shelf_life_days: number;
};

type UrgencyOption = {
    value: string;
    label: string;
};

type Availability = Record<string, Record<string, number>>;

type BloodBank = {
    id: number;
    name: string;
    address: string | null;
    phone: string | null;
    latitude: number | string | null;
    longitude: number | string | null;
    availability: Availability;
};

type AcceptedDonor = {
    id: number;
    name: string;
    phone: string | null;
    blood_group: string;
    distance_km: number;
};

type PatientRequest = {
    id: number;
    reference: string;
    hospital: string | null;
    blood_group: string;
    component: string;
    units_needed: number;
    status: string;
    donors_notified_count: number;
    broadcast_at: string;
    donors: AcceptedDonor[];
};

type DonorProfile = {
    id: number;
    name: string;
    phone: string;
    blood_group: string;
    city: string | null;
    is_verified: boolean;
    is_available: boolean;
};

type PageProps = {
    banks: BloodBank[];
    requests: PatientRequest[];
    donor: DonorProfile | null;
    options: {
        groups: BloodGroupOption[];
        components: ComponentOption[];
        urgencies: UrgencyOption[];
    };
    defaults: {
        radius_km: number;
        max_radius_km: number;
    };
    flash?: {
        success?: string | null;
        error?: string | null;
    };
};

type EmergencyForm = {
    hospital_id: string;
    blood_group: string;
    component: string;
    units_needed: string;
    urgency: string;
    radius_km: string;
    note: string;
};

type DonorForm = {
    name: string;
    phone: string;
    blood_group: string;
    latitude: string;
    longitude: string;
    city: string;
    last_donated_at: string;
    consent: boolean;
};

const breadcrumbs: BreadcrumbItem[] = [
    { title: "Blood bank", href: "/patient/blood-bank" },
];

export default function Index({
    banks,
    requests,
    donor,
    options,
    defaults,
    flash,
}: PageProps) {
    usePoll(30_000, { only: ["banks", "requests"] });

    const [selectedBankId, setSelectedBankId] = useState<number | null>(
        banks[0]?.id ?? null,
    );
    const { data, setData, post, processing, errors } = useForm<EmergencyForm>({
        hospital_id: banks[0] ? String(banks[0].id) : "",
        blood_group: options.groups[0]?.value ?? "",
        component: options.components[1]?.value ?? "",
        units_needed: "1",
        urgency: options.urgencies[1]?.value ?? "",
        radius_km: String(defaults.radius_km),
        note: "",
    });
    const donorForm = useForm<DonorForm>({
        name: "",
        phone: "",
        blood_group: options.groups[0]?.value ?? "",
        latitude: "",
        longitude: "",
        city: "",
        last_donated_at: "",
        consent: false,
    });
    const [locationError, setLocationError] = useState<string | null>(null);

    const selectedBank =
        banks.find((bank) => bank.id === selectedBankId) ?? banks[0] ?? null;
    const pageFlash = flash;

    const submitEmergencyRequest = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        post(route("patient.blood-bank.emergency.store"), {
            preserveScroll: true,
        });
    };

    const submitDonorProfile = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        donorForm.post(route("patient.blood-bank.donor-profile.store"), {
            preserveScroll: true,
        });
    };

    const useCurrentLocation = () => {
        if (!navigator.geolocation) {
            setLocationError("Location access is not available in this browser.");
            return;
        }

        navigator.geolocation.getCurrentPosition(
            ({ coords }) => {
                donorForm.setData("latitude", String(coords.latitude));
                donorForm.setData("longitude", String(coords.longitude));
                setLocationError(null);
            },
            () =>
                setLocationError(
                    "Could not read your location. Enter your coordinates manually.",
                ),
        );
    };

    const selectBank = (bank: BloodBank) => {
        setSelectedBankId(bank.id);
        setData("hospital_id", String(bank.id));
    };

    const mapUrl = (bank: BloodBank): string | null => {
        if (bank.latitude === null || bank.longitude === null) {
            return null;
        }

        const latitude = Number(bank.latitude);
        const longitude = Number(bank.longitude);
        const offset = 0.008;

        return `https://www.openstreetmap.org/export/embed.html?bbox=${longitude - offset}%2C${latitude - offset}%2C${longitude + offset}%2C${latitude + offset}&layer=mapnik&marker=${latitude}%2C${longitude}`;
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Blood bank & donor network" />
            <div className="flex flex-1 flex-col gap-6 p-4 md:p-6">
                <section className="rounded-xl bg-rose-700 p-6 text-white md:p-8">
                    <p className="text-sm font-medium text-rose-100">
                        Blood bank & donor network
                    </p>
                    <h1 className="mt-2 text-2xl font-semibold md:text-3xl">
                        Find nearby blood. Request help fast.
                    </h1>
                    <p className="mt-2 max-w-2xl text-sm text-rose-100">
                        Check live availability at registered blood banks or
                        send an emergency alert to compatible donors nearby.
                    </p>
                </section>

                {pageFlash?.success && (
                    <div
                        role="status"
                        className="rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-900"
                    >
                        {pageFlash.success}
                    </div>
                )}
                {pageFlash?.error && (
                    <div
                        role="alert"
                        className="rounded-lg border border-rose-200 bg-rose-50 p-4 text-sm text-rose-900"
                    >
                        {pageFlash.error}
                    </div>
                )}

                <div className="grid gap-6 xl:grid-cols-[minmax(0,1.4fr)_minmax(20rem,0.8fr)]">
                    <section className="flex flex-col gap-4">
                        <div className="flex items-center justify-between gap-3">
                            <div>
                                <h2 className="text-xl font-semibold text-foreground">
                                    Blood bank directory
                                </h2>
                                <p className="mt-1 text-sm text-muted-foreground">
                                    Availability refreshes automatically every
                                    30 seconds.
                                </p>
                            </div>
                            <Activity
                                className="size-5 text-rose-700"
                                aria-hidden="true"
                            />
                        </div>

                        {banks.length === 0 ? (
                            <div className="rounded-xl border border-dashed border-sidebar-border p-8 text-center">
                                <Hospital
                                    className="mx-auto size-8 text-muted-foreground"
                                    aria-hidden="true"
                                />
                                <p className="mt-3 font-medium text-foreground">
                                    No blood banks are listed yet.
                                </p>
                                <p className="mt-1 text-sm text-muted-foreground">
                                    Hospital staff need to assign a storage unit
                                    to an approved hospital before it appears
                                    here.
                                </p>
                            </div>
                        ) : (
                            <>
                                <div className="grid gap-3 md:grid-cols-2">
                                    {banks.map((bank) => (
                                        <article
                                            key={bank.id}
                                            className={`rounded-xl border bg-card p-4 transition hover:border-rose-300 hover:shadow-sm ${
                                                selectedBank?.id === bank.id
                                                    ? "border-rose-500 ring-1 ring-rose-500"
                                                    : "border-sidebar-border/70"
                                            }`}
                                        >
                                            <span className="flex items-start justify-between gap-3">
                                                <button
                                                    type="button"
                                                    onClick={() =>
                                                        selectBank(bank)
                                                    }
                                                    aria-pressed={
                                                        selectedBank?.id ===
                                                        bank.id
                                                    }
                                                    className="text-left"
                                                >
                                                    <span className="font-semibold text-foreground">
                                                        {bank.name}
                                                    </span>
                                                    <span className="mt-1 flex items-start gap-1.5 text-sm text-muted-foreground">
                                                        <MapPin
                                                            className="mt-0.5 size-4 shrink-0"
                                                            aria-hidden="true"
                                                        />
                                                        {bank.address ??
                                                            "Address unavailable"}
                                                    </span>
                                                </button>
                                                {bank.phone && (
                                                    <a
                                                        href={`tel:${bank.phone}`}
                                                        aria-label={`Call ${bank.name}`}
                                                        className="inline-flex shrink-0 items-center gap-1 rounded-md bg-emerald-50 px-2.5 py-1.5 text-sm font-medium text-emerald-800 hover:bg-emerald-100"
                                                    >
                                                        <Phone
                                                            className="size-4"
                                                            aria-hidden="true"
                                                        />
                                                        Call
                                                    </a>
                                                )}
                                            </span>
                                            <button
                                                type="button"
                                                onClick={() => selectBank(bank)}
                                                className="mt-3 text-xs text-muted-foreground hover:text-rose-700"
                                            >
                                                View live stock and map
                                            </button>
                                        </article>
                                    ))}
                                </div>

                                {selectedBank && (
                                    <div className="grid gap-4 lg:grid-cols-2">
                                        <div className="overflow-hidden rounded-xl border border-sidebar-border/70 bg-card">
                                            {mapUrl(selectedBank) ? (
                                                <iframe
                                                    title={`${selectedBank.name} map`}
                                                    src={mapUrl(selectedBank)!}
                                                    className="h-72 w-full border-0"
                                                    loading="lazy"
                                                    referrerPolicy="no-referrer-when-downgrade"
                                                />
                                            ) : (
                                                <div className="flex h-72 items-center justify-center p-6 text-center text-sm text-muted-foreground">
                                                    Map location is not
                                                    available for this hospital
                                                    yet.
                                                </div>
                                            )}
                                            <div className="flex items-center justify-between gap-3 border-t border-sidebar-border/70 p-3">
                                                <span className="text-sm font-medium text-foreground">
                                                    {selectedBank.name}
                                                </span>
                                                {selectedBank.latitude !==
                                                    null &&
                                                    selectedBank.longitude !==
                                                        null && (
                                                        <a
                                                            href={`https://www.openstreetmap.org/?mlat=${selectedBank.latitude}&mlon=${selectedBank.longitude}#map=16/${selectedBank.latitude}/${selectedBank.longitude}`}
                                                            target="_blank"
                                                            rel="noopener noreferrer"
                                                            className="text-sm font-medium text-rose-700 hover:underline"
                                                        >
                                                            Directions
                                                        </a>
                                                    )}
                                            </div>
                                        </div>

                                        <div className="rounded-xl border border-sidebar-border/70 bg-card p-4">
                                            <h3 className="font-semibold text-foreground">
                                                Live blood availability
                                            </h3>
                                            <p className="mt-1 text-xs text-muted-foreground">
                                                Available, unexpired units by
                                                blood group and component.
                                            </p>
                                            <div className="mt-3 max-h-64 overflow-auto">
                                                <table className="w-full text-left text-xs">
                                                    <thead className="sticky top-0 bg-muted text-muted-foreground">
                                                        <tr>
                                                            <th className="p-2">
                                                                Group
                                                            </th>
                                                            {options.components.map(
                                                                (component) => (
                                                                    <th
                                                                        key={
                                                                            component.value
                                                                        }
                                                                        className="p-2"
                                                                    >
                                                                        {
                                                                            component.label
                                                                        }
                                                                    </th>
                                                                ),
                                                            )}
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        {options.groups.map(
                                                            (group) => (
                                                                <tr
                                                                    key={
                                                                        group.value
                                                                    }
                                                                    className="border-t border-sidebar-border/50"
                                                                >
                                                                    <th className="p-2 font-semibold text-foreground">
                                                                        {
                                                                            group.label
                                                                        }
                                                                    </th>
                                                                    {options.components.map(
                                                                        (
                                                                            component,
                                                                        ) => (
                                                                            <td
                                                                                key={
                                                                                    component.value
                                                                                }
                                                                                className="p-2 text-foreground"
                                                                            >
                                                                                {selectedBank
                                                                                    .availability[
                                                                                    group
                                                                                        .value
                                                                                ]?.[
                                                                                    component
                                                                                        .value
                                                                                ] ??
                                                                                    0}
                                                                            </td>
                                                                        ),
                                                                    )}
                                                                </tr>
                                                            ),
                                                        )}
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                )}
                            </>
                        )}
                    </section>

                    <section className="h-fit rounded-xl border border-sidebar-border/70 bg-card p-5">
                        <div className="flex items-center gap-3">
                            <span className="rounded-lg bg-rose-100 p-2 text-rose-700 dark:bg-rose-950 dark:text-rose-300">
                                <Siren className="size-5" aria-hidden="true" />
                            </span>
                            <div>
                                <h2 className="text-lg font-semibold text-foreground">
                                    Emergency donor request
                                </h2>
                                <p className="text-sm text-muted-foreground">
                                    Alert compatible, nearby donors.
                                </p>
                            </div>
                        </div>

                        <form
                            onSubmit={submitEmergencyRequest}
                            className="mt-5 flex flex-col gap-4"
                        >
                            <label className="grid gap-1.5 text-sm font-medium text-foreground">
                                Blood bank
                                <select
                                    value={data.hospital_id}
                                    onChange={(event) => {
                                        const bankId = Number(event.target.value);
                                        setSelectedBankId(
                                            Number.isNaN(bankId) ? null : bankId,
                                        );
                                        setData("hospital_id", event.target.value);
                                    }}
                                    required
                                    disabled={banks.length === 0}
                                    className="rounded-md border border-input bg-background px-3 py-2"
                                >
                                    <option value="">Select a blood bank</option>
                                    {banks.map((bank) => (
                                        <option key={bank.id} value={bank.id}>
                                            {bank.name}
                                        </option>
                                    ))}
                                </select>
                                {errors.hospital_id && (
                                    <span className="text-xs text-destructive">
                                        {errors.hospital_id}
                                    </span>
                                )}
                            </label>

                            <label className="grid gap-1.5 text-sm font-medium text-foreground">
                                Patient blood group
                                <select
                                    value={data.blood_group}
                                    onChange={(event) =>
                                        setData("blood_group", event.target.value)
                                    }
                                    required
                                    className="rounded-md border border-input bg-background px-3 py-2"
                                >
                                    {options.groups.map((group) => (
                                        <option
                                            key={group.value}
                                            value={group.value}
                                        >
                                            {group.label}
                                        </option>
                                    ))}
                                </select>
                                {errors.blood_group && (
                                    <span className="text-xs text-destructive">
                                        {errors.blood_group}
                                    </span>
                                )}
                            </label>

                            <label className="grid gap-1.5 text-sm font-medium text-foreground">
                                Blood component
                                <select
                                    value={data.component}
                                    onChange={(event) =>
                                        setData("component", event.target.value)
                                    }
                                    required
                                    className="rounded-md border border-input bg-background px-3 py-2"
                                >
                                    {options.components.map((component) => (
                                        <option
                                            key={component.value}
                                            value={component.value}
                                        >
                                            {component.label}
                                        </option>
                                    ))}
                                </select>
                                {errors.component && (
                                    <span className="text-xs text-destructive">
                                        {errors.component}
                                    </span>
                                )}
                            </label>

                            <div className="grid grid-cols-2 gap-3">
                                <label className="grid gap-1.5 text-sm font-medium text-foreground">
                                    Units needed
                                    <input
                                        type="number"
                                        min="1"
                                        max="50"
                                        value={data.units_needed}
                                        onChange={(event) =>
                                            setData(
                                                "units_needed",
                                                event.target.value,
                                            )
                                        }
                                        required
                                        className="rounded-md border border-input bg-background px-3 py-2"
                                    />
                                </label>
                                <label className="grid gap-1.5 text-sm font-medium text-foreground">
                                    Urgency
                                    <select
                                        value={data.urgency}
                                        onChange={(event) =>
                                            setData("urgency", event.target.value)
                                        }
                                        required
                                        className="rounded-md border border-input bg-background px-3 py-2"
                                    >
                                        {options.urgencies.map((urgency) => (
                                            <option
                                                key={urgency.value}
                                                value={urgency.value}
                                            >
                                                {urgency.label}
                                            </option>
                                        ))}
                                    </select>
                                </label>
                            </div>

                            <label className="grid gap-1.5 text-sm font-medium text-foreground">
                                Donor search radius (1–{defaults.max_radius_km} km)
                                <input
                                    type="number"
                                    min="1"
                                    max={defaults.max_radius_km}
                                    value={data.radius_km}
                                    onChange={(event) =>
                                        setData("radius_km", event.target.value)
                                    }
                                    required
                                    className="rounded-md border border-input bg-background px-3 py-2"
                                />
                            </label>

                            <label className="grid gap-1.5 text-sm font-medium text-foreground">
                                Note for donors (optional)
                                <textarea
                                    value={data.note}
                                    onChange={(event) =>
                                        setData("note", event.target.value)
                                    }
                                    maxLength={500}
                                    rows={3}
                                    className="rounded-md border border-input bg-background px-3 py-2"
                                />
                            </label>

                            <p className="text-xs text-muted-foreground">
                                Only verified, available, compatible donors
                                inside the selected bank’s radius are alerted.
                                Donor phone numbers appear here only after they
                                accept.
                            </p>
                            <button
                                type="submit"
                                disabled={processing || banks.length === 0}
                                className="inline-flex items-center justify-center rounded-md bg-rose-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-rose-800 disabled:cursor-not-allowed disabled:opacity-60"
                            >
                                {processing
                                    ? "Sending request..."
                                    : "Broadcast emergency request"}
                            </button>
                        </form>
                    </section>
                </div>

                <section className="rounded-xl border border-sidebar-border/70 bg-card p-5">
                    <div className="flex flex-wrap items-start justify-between gap-4">
                        <div>
                            <h2 className="text-xl font-semibold text-foreground">
                                Donor & recipient hub
                            </h2>
                            <p className="mt-1 text-sm text-muted-foreground">
                                Join the verified donor network or manage your
                                alert availability.
                            </p>
                        </div>
                        {donor && (
                            <span
                                className={`rounded-full px-3 py-1 text-xs font-semibold ${
                                    donor.is_verified
                                        ? "bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-200"
                                        : "bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-200"
                                }`}
                            >
                                {donor.is_verified
                                    ? "Verified donor"
                                    : "Pending verification"}
                            </span>
                        )}
                    </div>

                    {donor ? (
                        <div className="mt-4 flex flex-wrap items-center justify-between gap-4 rounded-lg bg-muted/50 p-4">
                            <div>
                                <p className="font-medium text-foreground">
                                    {donor.name} · {donor.blood_group}
                                    {donor.city ? ` · ${donor.city}` : ""}
                                </p>
                                <p className="mt-1 text-sm text-muted-foreground">
                                    {donor.is_verified
                                        ? "Verified donors can receive emergency alerts when available."
                                        : "Staff must verify your profile before you can receive alerts."}
                                </p>
                            </div>
                            <button
                                type="button"
                                onClick={() =>
                                    router.patch(
                                        route(
                                            "patient.blood-bank.donor-profile.availability",
                                        ),
                                        { is_available: !donor.is_available },
                                        { preserveScroll: true },
                                    )
                                }
                                className={`rounded-md px-4 py-2 text-sm font-semibold ${
                                    donor.is_available
                                        ? "bg-slate-200 text-slate-800 hover:bg-slate-300 dark:bg-slate-700 dark:text-slate-100"
                                        : "bg-emerald-700 text-white hover:bg-emerald-800"
                                }`}
                            >
                                {donor.is_available
                                    ? "Pause donor alerts"
                                    : "Resume donor alerts"}
                            </button>
                        </div>
                    ) : (
                        <form
                            onSubmit={submitDonorProfile}
                            className="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-4"
                        >
                            <label className="grid gap-1.5 text-sm font-medium text-foreground">
                                Name
                                <input
                                    value={donorForm.data.name}
                                    onChange={(event) =>
                                        donorForm.setData("name", event.target.value)
                                    }
                                    maxLength={255}
                                    required
                                    className="rounded-md border border-input bg-background px-3 py-2"
                                />
                                {donorForm.errors.name && (
                                    <span className="text-xs text-destructive">
                                        {donorForm.errors.name}
                                    </span>
                                )}
                            </label>
                            <label className="grid gap-1.5 text-sm font-medium text-foreground">
                                Phone
                                <input
                                    type="tel"
                                    value={donorForm.data.phone}
                                    onChange={(event) =>
                                        donorForm.setData("phone", event.target.value)
                                    }
                                    maxLength={40}
                                    required
                                    className="rounded-md border border-input bg-background px-3 py-2"
                                />
                                {donorForm.errors.phone && (
                                    <span className="text-xs text-destructive">
                                        {donorForm.errors.phone}
                                    </span>
                                )}
                            </label>
                            <label className="grid gap-1.5 text-sm font-medium text-foreground">
                                Blood group
                                <select
                                    value={donorForm.data.blood_group}
                                    onChange={(event) =>
                                        donorForm.setData(
                                            "blood_group",
                                            event.target.value,
                                        )
                                    }
                                    required
                                    className="rounded-md border border-input bg-background px-3 py-2"
                                >
                                    {options.groups.map((group) => (
                                        <option
                                            key={group.value}
                                            value={group.value}
                                        >
                                            {group.label}
                                        </option>
                                    ))}
                                </select>
                            </label>
                            <label className="grid gap-1.5 text-sm font-medium text-foreground">
                                City
                                <input
                                    value={donorForm.data.city}
                                    onChange={(event) =>
                                        donorForm.setData("city", event.target.value)
                                    }
                                    maxLength={120}
                                    className="rounded-md border border-input bg-background px-3 py-2"
                                />
                            </label>

                            <div className="sm:col-span-2 lg:col-span-4">
                                <button
                                    type="button"
                                    onClick={useCurrentLocation}
                                    className="text-sm font-semibold text-rose-700 hover:underline"
                                >
                                    Use my current location
                                </button>
                                {locationError && (
                                    <p
                                        role="alert"
                                        className="mt-1 text-xs text-destructive"
                                    >
                                        {locationError}
                                    </p>
                                )}
                            </div>
                            <label className="grid gap-1.5 text-sm font-medium text-foreground">
                                Latitude
                                <input
                                    type="number"
                                    step="any"
                                    min="-90"
                                    max="90"
                                    value={donorForm.data.latitude}
                                    onChange={(event) =>
                                        donorForm.setData(
                                            "latitude",
                                            event.target.value,
                                        )
                                    }
                                    required
                                    className="rounded-md border border-input bg-background px-3 py-2"
                                />
                                {donorForm.errors.latitude && (
                                    <span className="text-xs text-destructive">
                                        {donorForm.errors.latitude}
                                    </span>
                                )}
                            </label>
                            <label className="grid gap-1.5 text-sm font-medium text-foreground">
                                Longitude
                                <input
                                    type="number"
                                    step="any"
                                    min="-180"
                                    max="180"
                                    value={donorForm.data.longitude}
                                    onChange={(event) =>
                                        donorForm.setData(
                                            "longitude",
                                            event.target.value,
                                        )
                                    }
                                    required
                                    className="rounded-md border border-input bg-background px-3 py-2"
                                />
                                {donorForm.errors.longitude && (
                                    <span className="text-xs text-destructive">
                                        {donorForm.errors.longitude}
                                    </span>
                                )}
                            </label>
                            <label className="grid gap-1.5 text-sm font-medium text-foreground">
                                Last donated
                                <input
                                    type="date"
                                    value={donorForm.data.last_donated_at}
                                    onChange={(event) =>
                                        donorForm.setData(
                                            "last_donated_at",
                                            event.target.value,
                                        )
                                    }
                                    className="rounded-md border border-input bg-background px-3 py-2"
                                />
                            </label>
                            <div className="flex flex-col justify-end gap-3 lg:col-span-4">
                                <label className="flex items-start gap-2 text-sm text-foreground">
                                    <input
                                        type="checkbox"
                                        checked={donorForm.data.consent}
                                        onChange={(event) =>
                                            donorForm.setData(
                                                "consent",
                                                event.target.checked,
                                            )
                                        }
                                        required
                                        className="mt-1"
                                    />
                                    I agree to be contacted about compatible
                                    emergency blood requests.
                                </label>
                                {donorForm.errors.consent && (
                                    <span className="text-xs text-destructive">
                                        {donorForm.errors.consent}
                                    </span>
                                )}
                                <button
                                    type="submit"
                                    disabled={donorForm.processing}
                                    className="w-fit rounded-md bg-rose-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-rose-800 disabled:opacity-60"
                                >
                                    {donorForm.processing
                                        ? "Submitting..."
                                        : "Join donor network"}
                                </button>
                            </div>
                        </form>
                    )}
                </section>

                <section className="flex flex-col gap-4">
                    <div>
                        <h2 className="text-xl font-semibold text-foreground">
                            My emergency requests
                        </h2>
                        <p className="mt-1 text-sm text-muted-foreground">
                            Only donors who accept are shown with a direct call
                            option.
                        </p>
                    </div>
                    {requests.length === 0 ? (
                        <div className="rounded-xl border border-dashed border-sidebar-border p-6 text-sm text-muted-foreground">
                            You have not sent an emergency donor request.
                        </div>
                    ) : (
                        <div className="grid gap-4 lg:grid-cols-2">
                            {requests.map((request) => (
                                <article
                                    key={request.id}
                                    className="rounded-xl border border-sidebar-border/70 bg-card p-5"
                                >
                                    <div className="flex flex-wrap items-start justify-between gap-3">
                                        <div>
                                            <h3 className="font-semibold text-foreground">
                                                {request.blood_group}{" "}
                                                {request.component}
                                            </h3>
                                            <p className="mt-1 text-sm text-muted-foreground">
                                                {request.hospital ?? "Hospital"}{" "}
                                                · {request.units_needed} unit(s)
                                                · {request.reference}
                                            </p>
                                        </div>
                                        <span className="rounded-full bg-muted px-2.5 py-1 text-xs font-medium capitalize text-foreground">
                                            {request.status}
                                        </span>
                                    </div>

                                    {request.donors.length > 0 ? (
                                        <div className="mt-4 flex flex-col gap-2">
                                            {request.donors.map((donor) => (
                                                <div
                                                    key={donor.id}
                                                    className="flex flex-wrap items-center justify-between gap-3 rounded-lg bg-emerald-50 p-3 dark:bg-emerald-950/40"
                                                >
                                                    <div>
                                                        <p className="font-medium text-emerald-950 dark:text-emerald-100">
                                                            {donor.name}{" "}
                                                            <span className="text-sm font-normal">
                                                                ({donor.blood_group},{" "}
                                                                {donor.distance_km.toFixed(
                                                                    1,
                                                                )}{" "}
                                                                km)
                                                            </span>
                                                        </p>
                                                        <p className="text-xs text-emerald-800 dark:text-emerald-200">
                                                            Accepted your request
                                                        </p>
                                                    </div>
                                                    {donor.phone && (
                                                        <a
                                                            href={`tel:${donor.phone}`}
                                                            className="inline-flex items-center gap-2 rounded-md bg-emerald-700 px-3 py-2 text-sm font-semibold text-white hover:bg-emerald-800"
                                                        >
                                                            <Phone
                                                                className="size-4"
                                                                aria-hidden="true"
                                                            />
                                                            Call donor
                                                        </a>
                                                    )}
                                                </div>
                                            ))}
                                        </div>
                                    ) : (
                                        <p className="mt-4 text-sm text-muted-foreground">
                                            {request.status === "open"
                                                ? `Waiting for a response. ${request.donors_notified_count} donor(s) alerted.`
                                                : "No donors accepted this request."}
                                        </p>
                                    )}

                                    {request.status === "open" && (
                                        <button
                                            type="button"
                                            onClick={() =>
                                                router.patch(
                                                    route(
                                                        "patient.blood-bank.emergency.cancel",
                                                        request.id,
                                                    ),
                                                    {},
                                                    { preserveScroll: true },
                                                )
                                            }
                                            className="mt-4 text-sm font-medium text-rose-700 hover:underline"
                                        >
                                            Cancel request
                                        </button>
                                    )}
                                </article>
                            ))}
                        </div>
                    )}
                </section>
            </div>
        </AppLayout>
    );
}
