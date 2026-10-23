
import { useForm } from "@inertiajs/react";
import type { FormEvent } from "react";
import { Button } from "@/Components/ui/button";
import { Input } from "@/Components/ui/input";
import { Label } from "@/Components/ui/label";

type PatientOption = {
    user_id: number;
    name: string;
    email: string | null;
};

type Profile = {
    id: number;
    user_id: number;
    last_menstrual_period: string;
    dating_confirmed_date: string | null;
    is_active: boolean;
};

type Props = {
    patients: PatientOption[];
    profile?: Profile;
};

export default function ProfileForm({ patients, profile }: Props) {
    const form = useForm({
        user_id: profile?.user_id
            ? String(profile.user_id)
            : "",
        last_menstrual_period:
            profile?.last_menstrual_period ?? "",
        dating_confirmed_date:
            profile?.dating_confirmed_date ?? "",
        is_active: profile?.is_active ?? true,
    });

    function submit(event: FormEvent<HTMLFormElement>) {
        event.preventDefault();

        const options = {
            onSuccess: () => form.clearErrors(),
        };

        if (profile) {
            form.transform((data) => ({
                ...data,
                user_id: Number(data.user_id),
                dating_confirmed_date:
                    data.dating_confirmed_date || null,
            }));

            form.put(
                route("admin.pregnancy.profiles.update", profile.id),
                options
            );
        } else {
            form.transform((data) => ({
                ...data,
                user_id: Number(data.user_id),
                dating_confirmed_date:
                    data.dating_confirmed_date || null,
            }));

            form.post(
                route("admin.pregnancy.profiles.store"),
                options
            );
        }
    }

    return (
        <form onSubmit={submit} className="max-w-2xl space-y-6">
            <div className="space-y-2">
                <Label htmlFor="user_id">Patient</Label>
                <select
                    id="user_id"
                    value={form.data.user_id}
                    onChange={(event) =>
                        form.setData("user_id", event.target.value)
                    }
                    required
                    className="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                >
                    <option value="">Select a patient</option>

                    {patients.map((patient) => (
                        <option
                            key={patient.user_id}
                            value={patient.user_id}
                        >
                            {patient.name}
                            {patient.email ? ` (${patient.email})` : ""}
                        </option>
                    ))}
                </select>

                {form.errors.user_id && (
                    <p className="text-sm text-destructive">
                        {form.errors.user_id}
                    </p>
                )}
            </div>

            <div className="space-y-2">
                <Label htmlFor="last_menstrual_period">
                    Last Menstrual Period (LMP)
                </Label>
                <Input
                    id="last_menstrual_period"
                    type="date"
                    value={form.data.last_menstrual_period}
                    max={new Date().toISOString().slice(0, 10)}
                    onChange={(event) =>
                        form.setData(
                            "last_menstrual_period",
                            event.target.value
                        )
                    }
                    required
                />
                {form.errors.last_menstrual_period && (
                    <p className="text-sm text-destructive">
                        {form.errors.last_menstrual_period}
                    </p>
                )}
                <p className="text-xs text-muted-foreground">
                    The estimated due date is calculated as 280 days
                    after the LMP.
                </p>
            </div>

            <div className="space-y-2">
                <Label htmlFor="dating_confirmed_date">
                    Dating Confirmation Date (optional)
                </Label>
                <Input
                    id="dating_confirmed_date"
                    type="date"
                    value={form.data.dating_confirmed_date}
                    max={new Date().toISOString().slice(0, 10)}
                    onChange={(event) =>
                        form.setData(
                            "dating_confirmed_date",
                            event.target.value
                        )
                    }
                />
                {form.errors.dating_confirmed_date && (
                    <p className="text-sm text-destructive">
                        {form.errors.dating_confirmed_date}
                    </p>
                )}
            </div>

            <label className="flex items-center gap-2 text-sm">
                <input
                    type="checkbox"
                    checked={form.data.is_active}
                    onChange={(event) =>
                        form.setData("is_active", event.target.checked)
                    }
                />
                Active pregnancy tracking
            </label>

            {form.errors.is_active && (
                <p className="text-sm text-destructive">
                    {form.errors.is_active}
                </p>
            )}

            <Button type="submit" disabled={form.processing}>
                {form.processing
                    ? "Saving..."
                    : profile
                        ? "Update Profile"
                        : "Create Profile"}
            </Button>
        </form>
    );
}
