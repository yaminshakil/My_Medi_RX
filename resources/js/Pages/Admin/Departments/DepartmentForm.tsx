import React from 'react';
import { useForm } from '@inertiajs/react';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Textarea } from '@/Components/ui/textarea';
import type { Department, HospitalOption } from './types';

type FormData = {
    hospital_id: string;
    name: string;
    code: string;
    description: string;
    floor: string;
    phone_number: string;
    email: string;
    sort_order: number;
    status: boolean;
};

export default function DepartmentForm({
    department,
    hospitals,
    submitUrl,
    method = 'post',
}: {
    department?: Department;
    hospitals: HospitalOption[];
    submitUrl: string;
    method?: 'post' | 'put';
}) {
    const form = useForm<FormData>({
        hospital_id: String(department?.hospital_id ?? ''),
        name: department?.name ?? '',
        code: department?.code ?? '',
        description: department?.description ?? '',
        floor: department?.floor ?? '',
        phone_number: department?.phone_number ?? '',
        email: department?.email ?? '',
        sort_order: department?.sort_order ?? 0,
        status: department?.status ?? true,
    });

    const submit = (event: React.FormEvent) => {
        event.preventDefault();
        if (method === 'put') form.put(submitUrl);
        else form.post(submitUrl);
    };

    return (
        <form onSubmit={submit} className="space-y-6">
            <div className="grid gap-4 md:grid-cols-2">
                <div className="space-y-2">
                    <Label htmlFor="hospital_id">Hospital *</Label>
                    <select id="hospital_id" className="flex h-10 w-full rounded-md border bg-background px-3 py-2 text-sm"
                        value={form.data.hospital_id}
                        onChange={e => form.setData('hospital_id', e.target.value)} required>
                        <option value="">Select hospital</option>
                        {hospitals.map(h => <option key={h.id} value={h.id}>{h.hospital_name}</option>)}
                    </select>
                    {form.errors.hospital_id && <p className="text-sm text-destructive">{form.errors.hospital_id}</p>}
                </div>
                <div className="space-y-2">
                    <Label htmlFor="name">Department name *</Label>
                    <Input id="name" value={form.data.name} onChange={e => form.setData('name', e.target.value)} required />
                    {form.errors.name && <p className="text-sm text-destructive">{form.errors.name}</p>}
                </div>
                <div className="space-y-2">
                    <Label htmlFor="code">Department code</Label>
                    <Input id="code" value={form.data.code} onChange={e => form.setData('code', e.target.value)} />
                    {form.errors.code && <p className="text-sm text-destructive">{form.errors.code}</p>}
                </div>
                <div className="space-y-2">
                    <Label htmlFor="floor">Floor / location</Label>
                    <Input id="floor" value={form.data.floor} onChange={e => form.setData('floor', e.target.value)} />
                    {form.errors.floor && <p className="text-sm text-destructive">{form.errors.floor}</p>}
                </div>
                <div className="space-y-2">
                    <Label htmlFor="phone_number">Contact phone</Label>
                    <Input id="phone_number" value={form.data.phone_number} onChange={e => form.setData('phone_number', e.target.value)} />
                    {form.errors.phone_number && <p className="text-sm text-destructive">{form.errors.phone_number}</p>}
                </div>
                <div className="space-y-2">
                    <Label htmlFor="email">Contact email</Label>
                    <Input id="email" type="email" value={form.data.email} onChange={e => form.setData('email', e.target.value)} />
                    {form.errors.email && <p className="text-sm text-destructive">{form.errors.email}</p>}
                </div>
                <div className="space-y-2">
                    <Label htmlFor="sort_order">Sort order</Label>
                    <Input id="sort_order" type="number" min={0} value={form.data.sort_order}
                        onChange={e => form.setData('sort_order', Number(e.target.value))} />
                </div>
                <div className="flex items-center gap-3 pt-7">
                    <input id="status" type="checkbox" checked={form.data.status}
                        onChange={e => form.setData('status', e.target.checked)} />
                    <Label htmlFor="status">Active department</Label>
                </div>
            </div>
            <div className="space-y-2">
                <Label htmlFor="description">Description</Label>
                <Textarea id="description" rows={4} value={form.data.description}
                    onChange={e => form.setData('description', e.target.value)} />
                {form.errors.description && <p className="text-sm text-destructive">{form.errors.description}</p>}
            </div>
            <div className="flex gap-3">
                <Button type="submit" disabled={form.processing}>
                    {form.processing ? 'Saving...' : department ? 'Update department' : 'Create department'}
                </Button>
                <Button type="button" variant="outline" onClick={() => window.history.back()}>Cancel</Button>
            </div>
        </form>
    );
}
