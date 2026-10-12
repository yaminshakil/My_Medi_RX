import { Link, useForm } from '@inertiajs/react';
import BloodBankLayout from '@/Components/BloodBank/BloodBankLayout';
import { Btn, Card, EmptyState, Field, Pagination, Pill, Select, formatDateTime, inputCls, td, th } from '@/Components/BloodBank/ui';

const statusTone = { open: 'rose', fulfilled: 'teal', cancelled: 'slate', expired: 'slate' };

export default function Index({ requests, options, defaults }) {
    const { data, setData, post, processing, errors } = useForm({
        blood_group: '',
        component: 'packed_red_cells',
        units_needed: 2,
        urgency: 'urgent',
        radius_km: defaults.radius_km,
        note: '',
    });

    const submit = (e) => {
        e.preventDefault();
        if (!window.confirm(`Alert verified ${data.blood_group} donors within ${data.radius_km} km now?`)) return;
        post(route('blood-bank.emergency.store'));
    };

    return (
        <BloodBankLayout title="Emergency requests">
            <form onSubmit={submit}>
                <Card title="Ask nearby donors for blood">
                    <div className="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                        <Field label="Patient blood group" error={errors.blood_group}>
                            <Select value={data.blood_group} onChange={(e) => setData('blood_group', e.target.value)} options={options.groups} placeholder="Select group" required />
                        </Field>
                        <Field label="Component" error={errors.component}>
                            <Select value={data.component} onChange={(e) => setData('component', e.target.value)} options={options.components} />
                        </Field>
                        <Field label="Units needed" error={errors.units_needed}>
                            <input type="number" min="1" max="50" className={inputCls} value={data.units_needed} onChange={(e) => setData('units_needed', e.target.value)} required />
                        </Field>
                        <Field label="Urgency" error={errors.urgency}>
                            <Select value={data.urgency} onChange={(e) => setData('urgency', e.target.value)} options={options.urgencies} />
                        </Field>
                        <Field label="Search radius (km)" error={errors.radius_km} hint={`Up to ${defaults.max_radius_km} km`}>
                            <input type="number" min="1" max={defaults.max_radius_km} step="0.5" className={inputCls} value={data.radius_km} onChange={(e) => setData('radius_km', e.target.value)} />
                        </Field>
                        <Field label="Note for donors" error={errors.note} className="sm:col-span-2 lg:col-span-3">
                            <input className={inputCls} value={data.note} onChange={(e) => setData('note', e.target.value)} placeholder="Where to go, who to ask for" />
                        </Field>
                    </div>
                    <div className="mt-6 flex items-center justify-between gap-3">
                        <p className="text-xs text-slate-500">Only verified donors who opted in, are compatible, and are past the minimum gap since their last donation are contacted.</p>
                        <Btn type="submit" disabled={processing || !data.blood_group}>Broadcast to donors</Btn>
                    </div>
                </Card>
            </form>

            <Card title="Recent requests" className="overflow-hidden">
                {requests.data.length === 0 ? (
                    <EmptyState>No emergency requests have been sent.</EmptyState>
                ) : (
                    <div className="-m-5 overflow-x-auto">
                        <table className="min-w-full divide-y divide-slate-200">
                            <thead className="bg-slate-50">
                                <tr>
                                    <th className={th}>Reference</th>
                                    <th className={th}>Request</th>
                                    <th className={th}>Sent</th>
                                    <th className={th}>Donors alerted</th>
                                    <th className={th}>Accepted</th>
                                    <th className={th}>Status</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-100">
                                {requests.data.map((r) => (
                                    <tr key={r.id} className="hover:bg-slate-50">
                                        <td className={td}>
                                            <Link href={route('blood-bank.emergency.show', r.id)} className="font-medium text-rose-800 hover:underline">{r.reference}</Link>
                                        </td>
                                        <td className={td}>{r.units_needed} × {r.blood_group} {r.component_label.toLowerCase()}</td>
                                        <td className={td}>{formatDateTime(r.broadcast_at)}</td>
                                        <td className={td}>{r.donors_notified_count}</td>
                                        <td className={td}>{r.accepted_count}</td>
                                        <td className={td}><Pill tone={statusTone[r.status]}>{r.status.charAt(0).toUpperCase() + r.status.slice(1)}</Pill></td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                )}
            </Card>
            <Pagination links={requests.links} />
        </BloodBankLayout>
    );
}
