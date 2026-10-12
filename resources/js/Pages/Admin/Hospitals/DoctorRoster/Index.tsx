import React, { FormEvent, useState } from 'react';
import { Head, router, useForm } from '@inertiajs/react';
import AppLayout from '@/Layouts/app-layout';

// Adapt these import paths to your project's shadcn/ui component locations.
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/Components/ui/tabs';
import DoctorAssignmentFields from './DoctorAssignmentFields';
import dayjs from 'dayjs';

type LinkItem = { url: string | null; label: string; active: boolean };
type Paginated<T> = { data: T[]; links: LinkItem[]; current_page: number; last_page: number; total: number };
type Shift = { id: number; hospital_id: number; name: string; start_time: string; end_time: string; is_active: boolean };
type Availability = {
    id: number; day_of_week: number; start_time: string; end_time: string;
    slot_duration: number; is_available: boolean; doctor?: { id: number; name: string };
    department?: { id: number; name: string }; shift?: { id: number; name: string } | null;
};
type Roster = {
    id: number; roster_date: string; start_time: string; end_time: string; status: string;
    doctor?: { id: number; name: string }; department?: { id: number; name: string };
    shift?: { id: number; name: string } | null; room_label?: string | null;
};
type Leave = {
    id: number; start_date: string; end_date: string; leave_type: string; reason?: string;
    status: string; doctor?: { id: number; name: string };
};

type Props = {
    shifts: Shift[];
    availabilities: Paginated<Availability>;
    rosters: Paginated<Roster>;
    leaves: Paginated<Leave>;
    days: string[];
    hospitals: { id: number; hospital_name: string }[];
};

export default function Index({ shifts, availabilities, rosters, leaves, days, hospitals }: Props) {
    const [activeTab, setActiveTab] = useState('availability');

    const shiftForm = useForm({
        hospital_id: '',
        name: '',
        start_time: '09:00',
        end_time: '17:00',
        break_start: '',
        break_end: '',
        is_active: true,
    });

    const availabilityForm = useForm({
        hospital_id: '',
        department_id: '',
        doctor_id: '',
        shift_id: '',
        day_of_week: '0',
        start_time: '09:00',
        end_time: '13:00',
        slot_duration: '15',
        max_patients: '',
        room_label: '',
        is_available: true,
    });

    const rosterForm = useForm({
        hospital_id: '',
        department_id: '',
        doctor_id: '',
        shift_id: '',
        roster_date: new Date().toISOString().slice(0, 10),
        start_time: '09:00',
        end_time: '17:00',
        room_label: '',
        status: 'scheduled',
        notes: '',
    });

    const leaveForm = useForm({
        hospital_id: '',
        doctor_id: '',
        start_date: new Date().toISOString().slice(0, 10),
        end_date: new Date().toISOString().slice(0, 10),
        start_time: '',
        end_time: '',
        leave_type: 'annual',
        reason: '',
    });

    const submit = (event: FormEvent, form: any, url: string) => {
        event.preventDefault();
        form.post(url, { preserveScroll: true, onSuccess: () => form.reset() });
    };

    const remove = (url: string) => {
        if (window.confirm('Delete this item?')) router.delete(url, { preserveScroll: true });
    };

    const pagination = (items: { links: LinkItem[] }) => (
        <nav className="mt-4 flex flex-wrap gap-2" aria-label="Pagination">
            {items.links.map((link, index) => (
                <Button
                    key={`${link.label}-${index}`}
                    type="button"
                    size="sm"
                    variant={link.active ? 'default' : 'outline'}
                    disabled={!link.url}
                    onClick={() => link.url && router.visit(link.url, { preserveScroll: true, preserveState: true })}
                    dangerouslySetInnerHTML={{ __html: link.label }}
                />
            ))}
        </nav>
    );

    return (
        <AppLayout>
            <Head title="Doctor Availability & Rostering" />
            <div className="space-y-6 p-4 md:p-6">
                <div>
                    <h1 className="text-2xl font-semibold tracking-tight">Doctor Availability & Rostering</h1>
                    <p className="text-sm text-muted-foreground">Manage shifts, weekly consultation hours, dated duties, and leave.</p>
                </div>

                <Tabs value={activeTab} onValueChange={setActiveTab}>
                    <TabsList className="flex h-auto flex-wrap">
                        <TabsTrigger value="availability">Weekly availability</TabsTrigger>
                        <TabsTrigger value="shifts">Shifts</TabsTrigger>
                        <TabsTrigger value="rosters">Duty roster</TabsTrigger>
                        <TabsTrigger value="leaves">Leave requests</TabsTrigger>
                    </TabsList>

                    <TabsContent value="shifts" className="space-y-4">
                        <Card>
                            <CardHeader><CardTitle>Create shift</CardTitle></CardHeader>
                            <CardContent>
                                <form className="grid gap-4 md:grid-cols-3" onSubmit={(e) => submit(e, shiftForm, route('admin.doctor-shifts.store'))}>
                                    <div className="space-y-2">
                                        <Label htmlFor="hospital_id">Hospital *</Label>
                                        <select id="hospital_id" className="flex h-10 w-full rounded-md border bg-background px-3 py-2 text-sm"
                                            value={shiftForm.data.hospital_id}
                                            onChange={e => shiftForm.setData('hospital_id', e.target.value)} required>
                                            <option value="">Select hospital</option>
                                            {hospitals.map(h => <option key={h.id} value={h.id}>{h.hospital_name}</option>)}
                                        </select>
                                        {shiftForm.errors.hospital_id && <p className="text-sm text-destructive">{shiftForm.errors.hospital_id}</p>}
                                    </div>
                                    <div><Label>Shift name</Label><Input value={shiftForm.data.name} onChange={e => shiftForm.setData('name', e.target.value)} placeholder="Morning" required /></div>
                                    <div><Label>Start</Label><Input type="time" value={shiftForm.data.start_time} onChange={e => shiftForm.setData('start_time', e.target.value)} required /></div>
                                    <div><Label>End</Label><Input type="time" value={shiftForm.data.end_time} onChange={e => shiftForm.setData('end_time', e.target.value)} required /></div>
                                    <div><Label>Break start</Label><Input type="time" value={shiftForm.data.break_start} onChange={e => shiftForm.setData('break_start', e.target.value)} /></div>
                                    <div><Label>Break end</Label><Input type="time" value={shiftForm.data.break_end} onChange={e => shiftForm.setData('break_end', e.target.value)} /></div>
                                    <div className="md:col-span-3"><Button disabled={shiftForm.processing}>Save shift</Button></div>
                                </form>
                            </CardContent>
                        </Card>
                        <Card><CardHeader><CardTitle>Shift definitions</CardTitle></CardHeader><CardContent>
                            <div className="overflow-x-auto"><table className="w-full text-sm"><thead><tr className="border-b text-left"><th className="p-2">Name</th><th className="p-2">Start</th><th className="p-2">End</th><th className="p-2">Status</th><th className="p-2">Action</th></tr></thead>
                                <tbody>{shifts.map(s => <tr key={s.id} className="border-b"><td className="p-2">{s.name}</td><td className="p-2">{s.start_time}</td><td className="p-2">{s.end_time}</td><td className="p-2">{s.is_active ? 'Active' : 'Inactive'}</td><td className="p-2"><Button variant="destructive" size="sm" onClick={() => remove(route('admin.doctor-shifts.destroy', s.id))}>Delete</Button></td></tr>)}</tbody>
                            </table></div>
                        </CardContent></Card>
                    </TabsContent>

                    <TabsContent value="availability" className="space-y-4">
                        <Card><CardHeader><CardTitle>Add weekly consultation hours</CardTitle></CardHeader><CardContent>
                            <form className="grid gap-4 md:grid-cols-3" onSubmit={(e) => submit(e, availabilityForm, route('admin.doctor-availabilities.store'))}>
                                <div className="md:col-span-3">
                                    <DoctorAssignmentFields
                                        hospitals={hospitals}
                                        shifts={shifts}
                                        hospitalId={availabilityForm.data.hospital_id}
                                        departmentId={availabilityForm.data.department_id}
                                        doctorId={availabilityForm.data.doctor_id}
                                        shiftId={availabilityForm.data.shift_id}
                                        errors={availabilityForm.errors}
                                        setHospitalId={(value) => availabilityForm.setData('hospital_id', value)}
                                        setDepartmentId={(value) => availabilityForm.setData('department_id', value)}
                                        setDoctorId={(value) => availabilityForm.setData('doctor_id', value)}
                                        setShiftId={(value) => availabilityForm.setData('shift_id', value)}
                                    />
                                </div>
                                <div><Label>Visiting day</Label><select className="h-10 w-full rounded-md border bg-background px-3" value={availabilityForm.data.day_of_week} onChange={e => availabilityForm.setData('day_of_week', e.target.value)}>{days.map((d, i) => <option key={d} value={i}>{d}</option>)}</select></div>
                                <div><Label>Slot duration (minutes)</Label><Input type="number" min="5" max="120" value={availabilityForm.data.slot_duration} onChange={e => availabilityForm.setData('slot_duration', e.target.value)} required /></div>
                                <div><Label>Consultation starts</Label><Input type="time" value={availabilityForm.data.start_time} onChange={e => availabilityForm.setData('start_time', e.target.value)} required /></div>
                                <div><Label>Consultation ends</Label><Input type="time" value={availabilityForm.data.end_time} onChange={e => availabilityForm.setData('end_time', e.target.value)} required /></div>
                                <div><Label>Room / chamber</Label><Input value={availabilityForm.data.room_label} onChange={e => availabilityForm.setData('room_label', e.target.value)} /></div>
                                <div className="md:col-span-3"><Button disabled={availabilityForm.processing}>Save availability</Button></div>
                            </form>
                        </CardContent></Card>
                        <Card><CardHeader><CardTitle>Weekly schedules</CardTitle></CardHeader><CardContent>
                            <div className="overflow-x-auto"><table className="w-full text-sm"><thead><tr className="border-b text-left"><th className="p-2">Doctor</th><th className="p-2">Department</th><th className="p-2">Shift</th><th className="p-2">Day</th><th className="p-2">Hours</th><th className="p-2">Slot</th><th className="p-2">Action</th></tr></thead>
                                <tbody>{availabilities.data.map(a => <tr key={a.id} className="border-b"><td className="p-2">{a.doctor?.name ?? `#${a.id}`}</td><td className="p-2">{a.department?.name ?? '—'}</td><td className="p-2">{a.shift?.name ?? '—'}</td><td className="p-2">{days[a.day_of_week]}</td><td className="p-2">{a.start_time}–{a.end_time}</td><td className="p-2">{a.slot_duration} min</td><td className="p-2"><Button variant="destructive" size="sm" onClick={() => remove(route('admin.doctor-availabilities.destroy', a.id))}>Delete</Button></td></tr>)}</tbody>
                            </table></div>{pagination(availabilities)}
                        </CardContent></Card>
                    </TabsContent>

                    <TabsContent value="rosters" className="space-y-4">
                        <Card><CardHeader><CardTitle>Assign dated duty</CardTitle></CardHeader><CardContent>
                            <form className="grid gap-4 md:grid-cols-3" onSubmit={(e) => submit(e, rosterForm, route('admin.doctor-rosters.store'))}>
                                <div className="md:col-span-3">
                                    <DoctorAssignmentFields
                                        hospitals={hospitals}
                                        shifts={shifts}
                                        hospitalId={rosterForm.data.hospital_id}
                                        departmentId={rosterForm.data.department_id}
                                        doctorId={rosterForm.data.doctor_id}
                                        shiftId={rosterForm.data.shift_id}
                                        errors={rosterForm.errors}
                                        setHospitalId={(value) => rosterForm.setData('hospital_id', value)}
                                        setDepartmentId={(value) => rosterForm.setData('department_id', value)}
                                        setDoctorId={(value) => rosterForm.setData('doctor_id', value)}
                                        setShiftId={(value) => rosterForm.setData('shift_id', value)}
                                    />
                                </div>
                                <div><Label>Duty date</Label><Input type="date" value={rosterForm.data.roster_date} onChange={e => rosterForm.setData('roster_date', e.target.value)} required /></div>
                                <div><Label>Room / chamber</Label><Input value={rosterForm.data.room_label} onChange={e => rosterForm.setData('room_label', e.target.value)} /></div>
                                <div><Label>Starts</Label><Input type="time" value={rosterForm.data.start_time} onChange={e => rosterForm.setData('start_time', e.target.value)} required /></div>
                                <div><Label>Ends</Label><Input type="time" value={rosterForm.data.end_time} onChange={e => rosterForm.setData('end_time', e.target.value)} required /></div>
                                <div><Label>Status</Label><select className="h-10 w-full rounded-md border bg-background px-3" value={rosterForm.data.status} onChange={e => rosterForm.setData('status', e.target.value)}><option value="scheduled">Scheduled</option><option value="completed">Completed</option><option value="cancelled">Cancelled</option></select></div>
                                <div className="md:col-span-3"><Label>Notes</Label><Input value={rosterForm.data.notes} onChange={e => rosterForm.setData('notes', e.target.value)} /></div>
                                <div className="md:col-span-3"><Button disabled={rosterForm.processing}>Assign duty</Button></div>
                            </form>
                        </CardContent></Card>
                        <Card><CardHeader><CardTitle>Dated duties</CardTitle></CardHeader><CardContent>
                            <div className="overflow-x-auto"><table className="w-full text-sm"><thead><tr className="border-b text-left"><th className="p-2">Date</th><th className="p-2">Doctor</th><th className="p-2">Department</th><th className="p-2">Time</th><th className="p-2">Room</th><th className="p-2">Status</th><th className="p-2">Action</th></tr></thead>
                                <tbody>{rosters.data.map(r => <tr key={r.id} className="border-b"><td className="p-2">{dayjs(r.roster_date, ['YYYY-MM-DD']).format(
                                    'YYYY-MM-DD',
                                )}</td><td className="p-2">{r.doctor?.name ?? '—'}</td><td className="p-2">{r.department?.name ?? '—'}</td><td className="p-2">{r.start_time}–{r.end_time}</td><td className="p-2">{r.room_label ?? '—'}</td><td className="p-2">{r.status}</td><td className="p-2"><Button variant="destructive" size="sm" onClick={() => remove(route('admin.doctor-rosters.destroy', r.id))}>Delete</Button></td></tr>)}</tbody>
                            </table></div>{pagination(rosters)}
                        </CardContent></Card>
                    </TabsContent>

                    <TabsContent value="leaves" className="space-y-4">
                        <Card><CardHeader><CardTitle>Submit leave request</CardTitle></CardHeader><CardContent>
                            <form className="grid gap-4 md:grid-cols-3" onSubmit={(e) => submit(e, leaveForm, route('admin.doctor-leaves.store'))}>
                                <div className="md:col-span-3">
                                    <DoctorAssignmentFields
                                        hospitals={hospitals}
                                        shifts={shifts}
                                        hospitalId={leaveForm.data.hospital_id}
                                        departmentId={leaveForm.data.department_id}
                                        doctorId={leaveForm.data.doctor_id}
                                        shiftId={leaveForm.data.shift_id}
                                        errors={leaveForm.errors}
                                        setHospitalId={(value) => availabilityForm.setData('hospital_id', value)}
                                        setDepartmentId={(value) => availabilityForm.setData('department_id', value)}
                                        setDoctorId={(value) => availabilityForm.setData('doctor_id', value)}
                                        setShiftId={(value) => availabilityForm.setData('shift_id', value)}
                                    />
                                </div>
                                <div><Label>Leave type</Label><select className="h-10 w-full rounded-md border bg-background px-3" value={leaveForm.data.leave_type} onChange={e => leaveForm.setData('leave_type', e.target.value)}><option value="annual">Annual</option><option value="sick">Sick</option><option value="emergency">Emergency</option><option value="other">Other</option></select></div>
                                <div><Label>Start date</Label><Input type="date" value={leaveForm.data.start_date} onChange={e => leaveForm.setData('start_date', e.target.value)} required /></div>
                                <div><Label>End date</Label><Input type="date" value={leaveForm.data.end_date} onChange={e => leaveForm.setData('end_date', e.target.value)} required /></div>
                                <div><Label>Reason</Label><Input value={leaveForm.data.reason} onChange={e => leaveForm.setData('reason', e.target.value)} /></div>
                                <div className="md:col-span-3"><Button disabled={leaveForm.processing}>Submit leave</Button></div>
                            </form>
                        </CardContent></Card>
                        <Card><CardHeader><CardTitle>Leave requests</CardTitle></CardHeader><CardContent>
                            <div className="overflow-x-auto"><table className="w-full text-sm"><thead><tr className="border-b text-left"><th className="p-2">Doctor</th><th className="p-2">Dates</th><th className="p-2">Type</th><th className="p-2">Status</th><th className="p-2">Actions</th></tr></thead>
                                <tbody>{leaves.data.map(l => <tr key={l.id} className="border-b"><td className="p-2">{l.doctor?.name ?? '—'}</td><td className="p-2">{l.start_date} – {l.end_date}</td><td className="p-2">{l.leave_type}</td><td className="p-2">{l.status}</td><td className="p-2"><div className="flex gap-2">{l.status === 'pending' && <><Button size="sm" onClick={() => router.patch(route('admin.doctor-leaves.approve', l.id), {}, { preserveScroll: true })}>Approve</Button><Button size="sm" variant="destructive" onClick={() => router.patch(route('admin.doctor-leaves.reject', l.id), {}, { preserveScroll: true })}>Reject</Button></>}</div></td></tr>)}</tbody>
                            </table></div>{pagination(leaves)}
                        </CardContent></Card>
                    </TabsContent>
                </Tabs>
            </div>
        </AppLayout>
    );
}
