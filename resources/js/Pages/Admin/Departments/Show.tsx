import React, { useState } from 'react';
import { Head, Link, router, useForm } from '@inertiajs/react';
import AppLayout from '@/Layouts/app-layout';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Badge } from '@/Components/ui/badge';
import type { Department, DoctorAssignment } from './types';

type DoctorOption = { id: number; name: string; email: string };

export default function Show({ department, availableDoctors, canAssignDoctors }: {
    department: Department; availableDoctors: DoctorOption[]; canAssignDoctors: boolean;
}) {
    const [editingDoctor, setEditingDoctor] = useState<DoctorAssignment | null>(null);
    const form = useForm({
        user_id: '',
        room_number: '',
        extension_number: '',
        is_department_head: false,
        status: true,
    });

    const editForm = useForm({
        room_number: '',
        extension_number: '',
        is_department_head: false,
        status: true,
    });

    const assign = (e: React.FormEvent) => {
        e.preventDefault();
        form.post(route('department.doctors.store', department.id), {
            onSuccess: () => form.reset(),
        });
    };

    const beginEdit = (doctor: DoctorAssignment) => {
        setEditingDoctor(doctor);
        editForm.setData({
            room_number: doctor.pivot.room_number ?? '',
            extension_number: doctor.pivot.extension_number ?? '',
            is_department_head: Boolean(doctor.pivot.is_department_head),
            status: Boolean(doctor.pivot.status),
        });
    };

    const saveEdit = (e: React.FormEvent) => {
        e.preventDefault();
        if (!editingDoctor) return;
        editForm.put(route('department.doctors.update', { department: department.id, user: editingDoctor.uuid }), {
            onSuccess: () => setEditingDoctor(null),
        });
    };

    const remove = (doctor: DoctorAssignment) => {
        if (window.confirm(`Remove ${doctor.name} from ${department.name}?`)) {
            router.delete(route('department.doctors.destroy', [department.id, doctor.id]));
        }
    };

    return (
        <AppLayout>
            <Head title={department.name} />
            <div className="mx-auto max-w-6xl space-y-6 p-6">
                <div className="flex flex-col justify-between gap-3 sm:flex-row sm:items-center">
                    <div><h1 className="text-2xl font-semibold">{department.name}</h1><p className="text-sm text-muted-foreground">{department.hospital?.hospital_name} · {department.code || 'No code'}</p></div>
                    <div className="flex gap-2"><Button variant="outline" asChild><Link href={route('department.index')}>Back</Link></Button><Button asChild><Link href={route('department.edit', department.id)}>Edit department</Link></Button></div>
                </div>

                <div className="grid gap-4 rounded-lg border p-4 md:grid-cols-3">
                    <div><p className="text-sm text-muted-foreground">Floor / Location</p><p>{department.floor || '—'}</p></div>
                    <div><p className="text-sm text-muted-foreground">Phone</p><p>{department.phone_number || '—'}</p></div>
                    <div><p className="text-sm text-muted-foreground">Email</p><p>{department.email || '—'}</p></div>
                    <div className="md:col-span-3"><p className="text-sm text-muted-foreground">Description</p><p className="whitespace-pre-wrap">{department.description || '—'}</p></div>
                </div>

                {canAssignDoctors && <form onSubmit={assign} className="space-y-4 rounded-lg border p-4">
                    <h2 className="text-lg font-semibold">Assign Doctor</h2>
                    <div className="grid gap-3 md:grid-cols-2 lg:grid-cols-4">
                        <div className="space-y-2 lg:col-span-2"><Label>Doctor *</Label>
                            <select className="h-10 w-full rounded-md border bg-background px-3 text-sm" value={form.data.user_id} onChange={e => form.setData('user_id', e.target.value)} required>
                                <option value="">Select doctor</option>{availableDoctors.map(d => <option key={d.id} value={d.id}>{d.name} ({d.email})</option>)}
                            </select>{form.errors.user_id && <p className="text-sm text-destructive">{form.errors.user_id}</p>}
                        </div>
                        <div className="space-y-2"><Label>Room number</Label><Input value={form.data.room_number} onChange={e => form.setData('room_number', e.target.value)} /></div>
                        <div className="space-y-2"><Label>Extension</Label><Input value={form.data.extension_number} onChange={e => form.setData('extension_number', e.target.value)} /></div>
                    </div>
                    <div className="flex flex-wrap items-center gap-5 text-sm">
                        <label className="flex items-center gap-2"><input type="checkbox" checked={form.data.is_department_head} onChange={e => form.setData('is_department_head', e.target.checked)} /> Department head</label>
                        <label className="flex items-center gap-2"><input type="checkbox" checked={form.data.status} onChange={e => form.setData('status', e.target.checked)} /> Active assignment</label>
                    </div>
                    <Button type="submit" disabled={form.processing}>Assign doctor</Button>
                </form>}

                <section className="space-y-3">
                    <h2 className="text-lg font-semibold">Assigned Doctors ({department.doctors?.length ?? 0})</h2>
                    {(department.doctors ?? []).length === 0 ? <div className="rounded-lg border p-8 text-center text-muted-foreground">No doctors assigned yet.</div> :
                        <div className="overflow-x-auto rounded-lg border"><table className="w-full text-sm">
                            <thead className="bg-muted/50"><tr className="text-left"><th className="p-3">Doctor</th><th className="p-3">Room</th><th className="p-3">Extension</th><th className="p-3">Role / Status</th><th className="p-3 text-right">Actions</th></tr></thead>
                            <tbody>{department.doctors!.map(doctor => <tr key={doctor.id} className="border-t">
                                <td className="p-3"><div className="font-medium">{doctor.name}</div><div className="text-muted-foreground">{doctor.email}</div></td>
                                <td className="p-3">{doctor.pivot.room_number || '—'}</td><td className="p-3">{doctor.pivot.extension_number || '—'}</td>
                                <td className="p-3"><div className="flex flex-wrap gap-2">{doctor.pivot.is_department_head && <Badge>Department head</Badge>}<Badge variant={doctor.pivot.status ? 'default' : 'secondary'}>{doctor.pivot.status ? 'Active' : 'Inactive'}</Badge></div></td>
                                <td className="p-3"><div className="flex justify-end gap-2">{canAssignDoctors && <><Button size="sm" variant="outline" onClick={() => beginEdit(doctor)}>Edit</Button><Button size="sm" variant="destructive" onClick={() => remove(doctor)}>Remove</Button></>}</div></td>
                            </tr>)}</tbody>
                        </table></div>
                    }
                </section>

                {editingDoctor && <div className="space-y-4 rounded-lg border p-4">
                    <h3 className="font-semibold">Edit assignment: {editingDoctor.name}</h3>
                    <form onSubmit={saveEdit} className="space-y-4">
                        <div className="grid gap-3 md:grid-cols-2">
                            <div className="space-y-2"><Label>Room number</Label><Input value={editForm.data.room_number} onChange={e => editForm.setData('room_number', e.target.value)} /></div>
                            <div className="space-y-2"><Label>Extension</Label><Input value={editForm.data.extension_number} onChange={e => editForm.setData('extension_number', e.target.value)} /></div>
                        </div>
                        <div className="flex gap-5 text-sm">
                            <label className="flex items-center gap-2"><input type="checkbox" checked={editForm.data.is_department_head} onChange={e => editForm.setData('is_department_head', e.target.checked)} /> Department head</label>
                            <label className="flex items-center gap-2"><input type="checkbox" checked={editForm.data.status} onChange={e => editForm.setData('status', e.target.checked)} /> Active</label>
                        </div>
                        <div className="flex gap-2"><Button type="submit" disabled={editForm.processing}>Save assignment</Button><Button type="button" variant="outline" onClick={() => setEditingDoctor(null)}>Cancel</Button></div>
                    </form>
                </div>}
            </div>
        </AppLayout>
    );
}
