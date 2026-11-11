import { router, useForm } from "@inertiajs/react";
import { type FormEvent, useEffect, useState } from "react";

type PatientProfile = {
    name: string;
    phone: string;
    city: string | null;
    blood_group: string | null;
};

type DonorProfile = {
    name: string;
    phone: string;
    blood_group: string;
    latitude: number | string;
    longitude: number | string;
    city: string | null;
    last_donated_at: string | null;
    is_verified: boolean;
    is_available: boolean;
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

type Props = {
    patient: PatientProfile;
    donor: DonorProfile | null;
};

const bloodGroups = ["A+", "A-", "B+", "B-", "AB+", "AB-", "O+", "O-"];

export default function DonorProfileToggle({ patient, donor }: Props) {
    const [showRegistration, setShowRegistration] = useState(false);
    const [locationError, setLocationError] = useState<string | null>(null);
    const [isAvailable, setIsAvailable] = useState(
        donor?.is_available ?? false,
    );
    const form = useForm<DonorForm>({
        name: donor?.name ?? patient.name,
        phone: donor?.phone ?? patient.phone,
        blood_group: donor?.blood_group ?? patient.blood_group ?? "",
        latitude: donor ? String(donor.latitude) : "",
        longitude: donor ? String(donor.longitude) : "",
        city: donor?.city ?? patient.city ?? "",
        last_donated_at: donor?.last_donated_at ?? "",
        consent: false,
    });

    useEffect(() => {
        setIsAvailable(donor?.is_available ?? false);
    }, [donor?.is_available]);

    const updateAvailability = (isAvailable: boolean) => {
        router.patch(
            route("patient.blood-bank.donor-profile.availability"),
            { is_available: isAvailable },
            {
                preserveScroll: true,
                onError: () => setIsAvailable(!isAvailable),
            },
        );
        setIsAvailable(isAvailable);
    };

    const useCurrentLocation = () => {
        if (!navigator.geolocation) {
            setLocationError(
                "Location access is not available in this browser.",
            );
            return;
        }

        navigator.geolocation.getCurrentPosition(
            ({ coords }) => {
                form.setData("latitude", String(coords.latitude));
                form.setData("longitude", String(coords.longitude));
                setLocationError(null);
            },
            () =>
                setLocationError(
                    "Could not read your location. Allow location access or try again.",
                ),
        );
    };

    const saveDonorProfile = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();

        if (donor) {
            form.patch(route("patient.blood-bank.donor-profile.update"), {
                preserveScroll: true,
            });

            return;
        }

        form.post(route("patient.blood-bank.donor-profile.store"), {
            preserveScroll: true,
        });
    };

    return (
        <section className="w-full rounded-xl border border-sidebar-border/70 bg-card p-5">
            <div className="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <h2 className="text-xl font-semibold text-foreground">
                        Blood donor profile
                    </h2>
                    <p className="mt-1 text-sm text-muted-foreground">
                        Opt in to compatible emergency requests using this
                        patient account.
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
            <p className="mt-2 text-sm text-muted-foreground">
                Blood bank staff verify donor details before emergency alerts
                are sent.
            </p>

            <label className="mt-4 flex items-center gap-3 text-sm font-medium text-foreground">
                <input
                    type="checkbox"
                    checked={donor ? isAvailable : showRegistration}
                    onChange={(event) => {
                        if (donor) {
                            updateAvailability(event.target.checked);
                            return;
                        }

                        setShowRegistration(event.target.checked);
                    }}
                    aria-label="Blood donor availability"
                    className="size-4 accent-rose-700"
                />
                {donor
                    ? donor.is_available
                        ? "Available for blood donor alerts"
                        : "Blood donor alerts paused"
                    : "Turn on Blood Donor"}
            </label>

            {(donor || showRegistration) && (
                <form
                    onSubmit={saveDonorProfile}
                    className="mt-5 grid gap-4 sm:grid-cols-2"
                >
                    <label className="grid gap-1.5 text-sm font-medium text-foreground">
                        Name
                        <input
                            value={form.data.name}
                            onChange={(event) =>
                                form.setData("name", event.target.value)
                            }
                            maxLength={255}
                            required
                            className="rounded-md border border-input bg-background px-3 py-2"
                        />
                        {form.errors.name && (
                            <span className="text-xs text-destructive">
                                {form.errors.name}
                            </span>
                        )}
                    </label>
                    <label className="grid gap-1.5 text-sm font-medium text-foreground">
                        Phone
                        <input
                            type="tel"
                            value={form.data.phone}
                            onChange={(event) =>
                                form.setData("phone", event.target.value)
                            }
                            maxLength={40}
                            required
                            className="rounded-md border border-input bg-background px-3 py-2"
                        />
                        {form.errors.phone && (
                            <span className="text-xs text-destructive">
                                {form.errors.phone}
                            </span>
                        )}
                    </label>
                    <label className="grid gap-1.5 text-sm font-medium text-foreground">
                        Blood group
                        <select
                            value={form.data.blood_group}
                            onChange={(event) =>
                                form.setData("blood_group", event.target.value)
                            }
                            required
                            className="rounded-md border border-input bg-background px-3 py-2"
                        >
                            <option value="">Select blood group</option>
                            {bloodGroups.map((bloodGroup) => (
                                <option key={bloodGroup} value={bloodGroup}>
                                    {bloodGroup}
                                </option>
                            ))}
                        </select>
                        {form.errors.blood_group && (
                            <span className="text-xs text-destructive">
                                {form.errors.blood_group}
                            </span>
                        )}
                    </label>
                    <label className="grid gap-1.5 text-sm font-medium text-foreground">
                        Last donation date
                        <input
                            type="date"
                            value={form.data.last_donated_at}
                            onChange={(event) =>
                                form.setData(
                                    "last_donated_at",
                                    event.target.value,
                                )
                            }
                            max={new Date().toISOString().slice(0, 10)}
                            className="rounded-md border border-input bg-background px-3 py-2"
                        />
                        {form.errors.last_donated_at && (
                            <span className="text-xs text-destructive">
                                {form.errors.last_donated_at}
                            </span>
                        )}
                    </label>
                    <label className="grid gap-1.5 text-sm font-medium text-foreground">
                        City
                        <input
                            value={form.data.city}
                            onChange={(event) =>
                                form.setData("city", event.target.value)
                            }
                            maxLength={120}
                            className="rounded-md border border-input bg-background px-3 py-2"
                        />
                        {form.errors.city && (
                            <span className="text-xs text-destructive">
                                {form.errors.city}
                            </span>
                        )}
                    </label>

                    <div className="grid gap-2 sm:col-span-2">
                        <p className="text-sm font-medium text-foreground">
                            Location for nearby matching
                        </p>
                        <div className="flex flex-wrap items-center gap-3">
                            <button
                                type="button"
                                onClick={useCurrentLocation}
                                className="rounded-md border border-input px-3 py-2 text-sm font-medium text-foreground hover:bg-muted"
                            >
                                Use current location
                            </button>
                            {form.data.latitude && form.data.longitude && (
                                <span className="text-xs text-muted-foreground">
                                    Location saved for donor matching.
                                </span>
                            )}
                        </div>
                        <div className="grid gap-4 sm:grid-cols-2">
                            <label className="grid gap-1.5 text-sm font-medium text-foreground">
                                Latitude
                                <input
                                    type="number"
                                    min="-90"
                                    max="90"
                                    step="any"
                                    value={form.data.latitude}
                                    onChange={(event) =>
                                        form.setData(
                                            "latitude",
                                            event.target.value,
                                        )
                                    }
                                    required
                                    className="rounded-md border border-input bg-background px-3 py-2"
                                />
                            </label>
                            <label className="grid gap-1.5 text-sm font-medium text-foreground">
                                Longitude
                                <input
                                    type="number"
                                    min="-180"
                                    max="180"
                                    step="any"
                                    value={form.data.longitude}
                                    onChange={(event) =>
                                        form.setData(
                                            "longitude",
                                            event.target.value,
                                        )
                                    }
                                    required
                                    className="rounded-md border border-input bg-background px-3 py-2"
                                />
                            </label>
                        </div>
                        {locationError && (
                            <p
                                role="alert"
                                className="text-xs text-destructive"
                            >
                                {locationError}
                            </p>
                        )}
                        {(form.errors.latitude || form.errors.longitude) && (
                            <p className="text-xs text-destructive">
                                Use current location or enter valid coordinates
                                for nearby donor matching.
                            </p>
                        )}
                    </div>

                    {!donor && (
                        <label className="flex items-start gap-2 text-sm text-foreground sm:col-span-2">
                            <input
                                type="checkbox"
                                checked={form.data.consent}
                                onChange={(event) =>
                                    form.setData(
                                        "consent",
                                        event.target.checked,
                                    )
                                }
                                required
                                className="mt-1"
                            />
                            I agree to share my location and be contacted about
                            compatible emergency blood requests.
                            {form.errors.consent && (
                                <span className="text-xs text-destructive">
                                    {form.errors.consent}
                                </span>
                            )}
                        </label>
                    )}

                    <button
                        type="submit"
                        disabled={form.processing}
                        className="w-fit rounded-md bg-rose-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-rose-800 disabled:cursor-not-allowed disabled:opacity-60 sm:col-span-2"
                    >
                        {form.processing
                            ? "Saving..."
                            : donor
                              ? "Save donor details"
                              : "Join donor network"}
                    </button>
                </form>
            )}
        </section>
    );
}
