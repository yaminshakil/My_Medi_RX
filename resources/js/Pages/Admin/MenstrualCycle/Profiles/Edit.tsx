import { Head, Link, useForm } from '@inertiajs/react';
import AppLayout from '@/Layouts/app-layout';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { type BreadcrumbItem } from '@/types';

type Profile = {
    id: number;
    name: string;
    email: string | null;
    last_period_start: string | null;
    cycle_length: number;
    period_length: number;
    is_active: boolean;
};

export default function Edit({ profile }: { profile: Profile }) {
    const breadcrumbs: BreadcrumbItem[] = [{ title: 'Edit Cycle Profile', href: '/admin/menstrual-cycle/profiles' }];
    const form = useForm({
        last_period_start: profile.last_period_start ?? '',
        cycle_length: String(profile.cycle_length),
        period_length: String(profile.period_length),
        is_active: profile.is_active,
    });

    const submit = (event: React.FormEvent) => {
        event.preventDefault();

        form.transform((data) => ({
            ...data,
            last_period_start: data.last_period_start || null,
            cycle_length: Number(data.cycle_length),
            period_length: Number(data.period_length),
        }));

        form.put(
            route('admin.menstrual-cycle.profiles.update', profile.id)
        );
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Edit Cycle Profile - ${profile.name}`} />

            <div className="mx-auto max-w-2xl space-y-6 p-4 md:p-6">
                <div>
                    <h1 className="text-2xl font-semibold">
                        Edit Cycle Profile
                    </h1>
                    <p className="text-sm text-muted-foreground">
                        {profile.name} · {profile.email}
                    </p>
                </div>

                <form onSubmit={submit} className="space-y-5 rounded-lg border p-5">
                    <div className="space-y-2">
                        <Label htmlFor="last_period_start">
                            First day of last period
                        </Label>
                        <Input
                            id="last_period_start"
                            type="date"
                            value={form.data.last_period_start}
                            max={new Date().toISOString().slice(0, 10)}
                            onChange={(event) => form.setData(
                                'last_period_start',
                                event.target.value
                            )}
                        />
                        {form.errors.last_period_start && (
                            <p className="text-sm text-destructive">
                                {form.errors.last_period_start}
                            </p>
                        )}
                    </div>

                    <div className="space-y-2">
                        <Label htmlFor="cycle_length">
                            Average cycle length (21–45 days)
                        </Label>
                        <Input
                            id="cycle_length"
                            type="number"
                            min={21}
                            max={45}
                            required
                            value={form.data.cycle_length}
                            onChange={(event) => form.setData(
                                'cycle_length',
                                event.target.value
                            )}
                        />
                        {form.errors.cycle_length && (
                            <p className="text-sm text-destructive">
                                {form.errors.cycle_length}
                            </p>
                        )}
                    </div>

                    <div className="space-y-2">
                        <Label htmlFor="period_length">
                            Period length (1–10 days)
                        </Label>
                        <Input
                            id="period_length"
                            type="number"
                            min={1}
                            max={10}
                            required
                            value={form.data.period_length}
                            onChange={(event) => form.setData(
                                'period_length',
                                event.target.value
                            )}
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
                            onChange={(event) => form.setData(
                                'is_active',
                                event.target.checked
                            )}
                        />
                        Enable cycle tracking
                    </label>

                    {form.errors.is_active && (
                        <p className="text-sm text-destructive">
                            {form.errors.is_active}
                        </p>
                    )}

                    <div className="flex gap-2">
                        <Button type="submit" disabled={form.processing}>
                            {form.processing ? 'Saving...' : 'Save changes'}
                        </Button>

                        <Button asChild type="button" variant="outline">
                            <Link href={route('admin.menstrual-cycle.profiles.index')}>
                                Cancel
                            </Link>
                        </Button>
                    </div>
                </form>
            </div>
        </AppLayout>
    );
}
