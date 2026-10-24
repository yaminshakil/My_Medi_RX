
import { useForm, Link } from '@inertiajs/react';
import type { FormEvent } from 'react';

import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';

export type CycleProfile = {
    id: number;
    name: string;
    email: string | null;
    last_period_start: string | null;
    cycle_length: number;
    period_length: number;
    is_active: boolean;
};

type Props = {
    profile?: CycleProfile;
};

export default function ProfileForm({ profile }: Props) {
    const form = useForm({
        last_period_start: profile?.last_period_start ?? '',
        cycle_length: String(profile?.cycle_length ?? 28),
        period_length: String(profile?.period_length ?? 5),
        is_active: profile?.is_active ?? true,
    });

    const submit = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();

        const payload = {
            last_period_start: form.data.last_period_start || null,
            cycle_length: Number(form.data.cycle_length),
            period_length: Number(form.data.period_length),
            is_active: form.data.is_active,
        };

        if (profile) {
            form.transform(() => payload);
            form.put(
                route('admin.menstrual-cycle.profiles.update', profile.id)
            );
        }
    };

    return (
        <form onSubmit={submit} className="space-y-5 rounded-lg border p-5">
            {profile && (
                <div>
                    <h2 className="font-semibold">{profile.name}</h2>
                    <p className="text-sm text-muted-foreground">
                        {profile.email}
                    </p>
                </div>
            )}

            <div className="space-y-2">
                <Label htmlFor="last_period_start">
                    First day of last period
                </Label>
                <Input
                    id="last_period_start"
                    type="date"
                    max={new Date().toISOString().slice(0, 10)}
                    value={form.data.last_period_start}
                    onChange={(event) =>
                        form.setData('last_period_start', event.target.value)
                    }
                />
                {form.errors.last_period_start && (
                    <p className="text-sm text-destructive">
                        {form.errors.last_period_start}
                    </p>
                )}
            </div>

            <div className="space-y-2">
                <Label htmlFor="cycle_length">Cycle length (21–45 days)</Label>
                <Input
                    id="cycle_length"
                    type="number"
                    min={21}
                    max={45}
                    required
                    value={form.data.cycle_length}
                    onChange={(event) =>
                        form.setData('cycle_length', event.target.value)
                    }
                />
                {form.errors.cycle_length && (
                    <p className="text-sm text-destructive">
                        {form.errors.cycle_length}
                    </p>
                )}
            </div>

            <div className="space-y-2">
                <Label htmlFor="period_length">Period length (1–10 days)</Label>
                <Input
                    id="period_length"
                    type="number"
                    min={1}
                    max={10}
                    required
                    value={form.data.period_length}
                    onChange={(event) =>
                        form.setData('period_length', event.target.value)
                    }
                />
                {form.errors.period_length && (
                    <p className="text-sm text-destructive">
                        {form.errors.period_length}
                    </p>
                )}
            </div>

            <label className="flex items-center gap-2 text-sm">
                <input
                    type="checkbox"
                    checked={form.data.is_active}
                    onChange={(event) =>
                        form.setData('is_active', event.target.checked)
                    }
                />
                Enable cycle tracking
            </label>

            <div className="flex gap-2">
                <Button type="submit" disabled={form.processing}>
                    {form.processing ? 'Saving...' : 'Save profile'}
                </Button>
                <Button asChild type="button" variant="outline">
                    <Link href={route('admin.menstrual-cycle.profiles.index')}>
                        Cancel
                    </Link>
                </Button>
            </div>
        </form>
    );
}
