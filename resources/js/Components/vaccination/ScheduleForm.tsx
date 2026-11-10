import { FormEvent } from 'react';
import { useForm } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { Checkbox } from '@/components/ui/checkbox';
import { Label } from '@/components/ui/label';
import {
    Select, SelectContent, SelectItem, SelectTrigger, SelectValue,
} from '@/components/ui/select';
import type { VaccinationSchedule, VaccineOption } from './types';

type FormData = {
    vaccine_id: string;
    schedule_name: string;
    dose_number: number;
    recommended_age_days: number | '';
    interval_days: number | '';
    notes: string;
    is_active: boolean;
};

type Props = {
    vaccines: VaccineOption[];
    schedule?: VaccinationSchedule;
};

export default function ScheduleForm({ vaccines, schedule }: Props) {
    const form = useForm<FormData>({
        vaccine_id: schedule ? String(schedule.vaccine_id) : '',
        schedule_name: schedule?.schedule_name ?? '',
        dose_number: schedule?.dose_number ?? 1,
        recommended_age_days: schedule?.recommended_age_days ?? '',
        interval_days: schedule?.interval_days ?? '',
        notes: schedule?.notes ?? '',
        is_active: schedule?.is_active ?? true,
    });

    const submit = (event: FormEvent) => {
        event.preventDefault();

        if (schedule) {
            form.put(route('vaccination-schedules.update', schedule.id), {
                preserveScroll: true,
            });
        } else {
            form.post(route('vaccination-schedules.store'), {
                preserveScroll: true,
            });
        }
    };

    return (
        <form onSubmit={submit} className="space-y-6">
            <div className="grid gap-5 md:grid-cols-2">
                <div className="space-y-2">
                    <Label htmlFor="vaccine_id">Vaccine <span className="text-destructive">*</span></Label>
                    <Select value={form.data.vaccine_id} onValueChange={(value) => form.setData('vaccine_id', value)}>
                        <SelectTrigger id="vaccine_id"><SelectValue placeholder="Select vaccine" /></SelectTrigger>
                        <SelectContent>
                            {vaccines.map((vaccine) => (
                                <SelectItem key={vaccine.id} value={String(vaccine.id)}>{vaccine.name}</SelectItem>
                            ))}
                        </SelectContent>
                    </Select>
                    {form.errors.vaccine_id && <p className="text-sm text-destructive">{form.errors.vaccine_id}</p>}
                </div>

                <div className="space-y-2">
                    <Label htmlFor="schedule_name">Schedule name</Label>
                    <Input id="schedule_name" value={form.data.schedule_name} onChange={(e) => form.setData('schedule_name', e.target.value)} placeholder="e.g. First dose" />
                    {form.errors.schedule_name && <p className="text-sm text-destructive">{form.errors.schedule_name}</p>}
                </div>

                <div className="space-y-2">
                    <Label htmlFor="dose_number">Dose number <span className="text-destructive">*</span></Label>
                    <Input id="dose_number" type="number" min={1} value={form.data.dose_number} onChange={(e) => form.setData('dose_number', Number(e.target.value))} />
                    {form.errors.dose_number && <p className="text-sm text-destructive">{form.errors.dose_number}</p>}
                </div>

                <div className="space-y-2">
                    <Label htmlFor="recommended_age_days">Recommended age (days)</Label>
                    <Input id="recommended_age_days" type="number" min={0} value={form.data.recommended_age_days} onChange={(e) => form.setData('recommended_age_days', e.target.value === '' ? '' : Number(e.target.value))} placeholder="e.g. 42" />
                    {form.errors.recommended_age_days && <p className="text-sm text-destructive">{form.errors.recommended_age_days}</p>}
                </div>

                <div className="space-y-2">
                    <Label htmlFor="interval_days">Interval from previous dose (days)</Label>
                    <Input id="interval_days" type="number" min={0} value={form.data.interval_days} onChange={(e) => form.setData('interval_days', e.target.value === '' ? '' : Number(e.target.value))} placeholder="e.g. 28" />
                    {form.errors.interval_days && <p className="text-sm text-destructive">{form.errors.interval_days}</p>}
                </div>
            </div>

            <div className="space-y-2">
                <Label htmlFor="notes">Notes</Label>
                <Textarea id="notes" rows={4} value={form.data.notes} onChange={(e) => form.setData('notes', e.target.value)} placeholder="Enter schedule notes..." />
                {form.errors.notes && <p className="text-sm text-destructive">{form.errors.notes}</p>}
            </div>

            <div className="flex items-center gap-2">
                <Checkbox id="is_active" checked={form.data.is_active} onCheckedChange={(checked) => form.setData('is_active', checked === true)} />
                <Label htmlFor="is_active">Active schedule</Label>
            </div>
            {form.errors.is_active && <p className="text-sm text-destructive">{form.errors.is_active}</p>}

            <div className="flex justify-end gap-2">
                <Button type="submit" disabled={form.processing}>
                    {form.processing ? 'Saving...' : schedule ? 'Update schedule' : 'Create schedule'}
                </Button>
            </div>
        </form>
    );
}
