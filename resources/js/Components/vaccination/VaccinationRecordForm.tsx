import { FormEvent, useMemo } from 'react';
import { useForm } from '@inertiajs/react';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Textarea } from '@/Components/ui/textarea';
import { Label } from '@/Components/ui/label';
import {
    Select, SelectContent, SelectItem, SelectTrigger, SelectValue,
} from '@/Components/ui/select';
import type { VaccinationRecord, VaccinationSchedule, VaccineOption } from './types';

type FormData = {
    vaccine_id: string;
    vaccination_schedule_id: string;
    dose_number: number;
    scheduled_date: string;
    administered_at: string;
    status: 'scheduled' | 'completed' | 'missed' | 'cancelled';
    provider_name: string;
    facility_name: string;
    batch_number: string;
    notes: string;
};

type Props = {
    vaccines: VaccineOption[];
    schedules: VaccinationSchedule[];
    record?: VaccinationRecord;
};

const toDateTimeLocal = (value: string | null | undefined) => {
    if (!value) return '';
    return value.length >= 16 ? value.slice(0, 16) : value;
};

export default function VaccinationRecordForm({ vaccines, schedules, record }: Props) {
    const form = useForm<FormData>({
        vaccine_id: record ? String(record.vaccine_id) : '',
        vaccination_schedule_id: record?.vaccination_schedule_id ? String(record.vaccination_schedule_id) : '',
        dose_number: record?.dose_number ?? 1,
        scheduled_date: record?.scheduled_date ?? '',
        administered_at: toDateTimeLocal(record?.administered_at),
        status: record?.status ?? 'scheduled',
        provider_name: record?.provider_name ?? '',
        facility_name: record?.facility_name ?? '',
        batch_number: record?.batch_number ?? '',
        notes: record?.notes ?? '',
    });

    const filteredSchedules = useMemo(
        () => schedules.filter((item) => String(item.vaccine_id) === form.data.vaccine_id),
        [schedules, form.data.vaccine_id],
    );

    const submit = (event: FormEvent) => {
        event.preventDefault();
        const options = { preserveScroll: true };

        if (record) {
            form.put(route('admin.vaccinations.update', record.id), options);
        } else {
            form.post(route('admin.vaccinations.store'), options);
        }
    };

    return (
        <form onSubmit={submit} className="space-y-6">
            <div className="grid gap-5 md:grid-cols-2">
                <div className="space-y-2">
                    <Label>Vaccine <span className="text-destructive">*</span></Label>
                    <Select
                        value={form.data.vaccine_id}
                        onValueChange={(value) => {
                            form.setData((current) => ({
                                ...current,
                                vaccine_id: value,
                                vaccination_schedule_id: '',
                                dose_number: 1,
                            }));
                        }}
                    >
                        <SelectTrigger><SelectValue placeholder="Select vaccine" /></SelectTrigger>
                        <SelectContent>
                            {vaccines.map((vaccine) => (
                                <SelectItem key={vaccine.id} value={String(vaccine.id)}>{vaccine.name}</SelectItem>
                            ))}
                        </SelectContent>
                    </Select>
                    {form.errors.vaccine_id && <p className="text-sm text-destructive">{form.errors.vaccine_id}</p>}
                </div>

                <div className="space-y-2">
                    <Label>Vaccination schedule</Label>
                    <Select
                        value={form.data.vaccination_schedule_id}
                        onValueChange={(value) => {
                            const selected = filteredSchedules.find((item) => String(item.id) === value);
                            form.setData((current) => ({
                                ...current,
                                vaccination_schedule_id: value,
                                dose_number: selected?.dose_number ?? current.dose_number,
                            }));
                        }}
                        disabled={!form.data.vaccine_id || filteredSchedules.length === 0}
                    >
                        <SelectTrigger><SelectValue placeholder="Select schedule (optional)" /></SelectTrigger>
                        <SelectContent>
                            {filteredSchedules.map((schedule) => (
                                <SelectItem key={schedule.id} value={String(schedule.id)}>
                                    {schedule.schedule_name || `Dose ${schedule.dose_number}`}
                                    {schedule.recommended_age_days !== null ? ` — ${schedule.recommended_age_days} days` : ''}
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>
                    {form.errors.vaccination_schedule_id && <p className="text-sm text-destructive">{form.errors.vaccination_schedule_id}</p>}
                </div>

                <div className="space-y-2">
                    <Label>Dose number <span className="text-destructive">*</span></Label>
                    <Input type="number" min={1} value={form.data.dose_number} onChange={(e) => form.setData('dose_number', Number(e.target.value))} />
                    {form.errors.dose_number && <p className="text-sm text-destructive">{form.errors.dose_number}</p>}
                </div>

                <div className="space-y-2">
                    <Label>Scheduled date</Label>
                    <Input type="date" value={form.data.scheduled_date} onChange={(e) => form.setData('scheduled_date', e.target.value)} />
                    {form.errors.scheduled_date && <p className="text-sm text-destructive">{form.errors.scheduled_date}</p>}
                </div>

                <div className="space-y-2">
                    <Label>Administered at</Label>
                    <Input type="datetime-local" value={form.data.administered_at} onChange={(e) => form.setData('administered_at', e.target.value)} />
                    {form.errors.administered_at && <p className="text-sm text-destructive">{form.errors.administered_at}</p>}
                </div>

                <div className="space-y-2">
                    <Label>Status</Label>
                    <Select value={form.data.status} onValueChange={(value: FormData['status']) => form.setData('status', value)}>
                        <SelectTrigger><SelectValue /></SelectTrigger>
                        <SelectContent>
                            <SelectItem value="scheduled">Scheduled</SelectItem>
                            <SelectItem value="completed">Completed</SelectItem>
                            <SelectItem value="missed">Missed</SelectItem>
                            <SelectItem value="cancelled">Cancelled</SelectItem>
                        </SelectContent>
                    </Select>
                    {form.errors.status && <p className="text-sm text-destructive">{form.errors.status}</p>}
                </div>

                <div className="space-y-2">
                    <Label>Provider name</Label>
                    <Input value={form.data.provider_name} onChange={(e) => form.setData('provider_name', e.target.value)} />
                    {form.errors.provider_name && <p className="text-sm text-destructive">{form.errors.provider_name}</p>}
                </div>

                <div className="space-y-2">
                    <Label>Facility name</Label>
                    <Input value={form.data.facility_name} onChange={(e) => form.setData('facility_name', e.target.value)} />
                    {form.errors.facility_name && <p className="text-sm text-destructive">{form.errors.facility_name}</p>}
                </div>

                <div className="space-y-2">
                    <Label>Batch number</Label>
                    <Input value={form.data.batch_number} onChange={(e) => form.setData('batch_number', e.target.value)} />
                    {form.errors.batch_number && <p className="text-sm text-destructive">{form.errors.batch_number}</p>}
                </div>
            </div>

            <div className="space-y-2">
                <Label>Notes</Label>
                <Textarea rows={4} value={form.data.notes} onChange={(e) => form.setData('notes', e.target.value)} />
                {form.errors.notes && <p className="text-sm text-destructive">{form.errors.notes}</p>}
            </div>

            <div className="flex justify-end">
                <Button type="submit" disabled={form.processing}>
                    {form.processing ? 'Saving...' : record ? 'Update record' : 'Save vaccination'}
                </Button>
            </div>
        </form>
    );
}
