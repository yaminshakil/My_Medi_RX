import { router } from '@inertiajs/react';
import BloodBankLayout from '@/Components/BloodBank/BloodBankLayout';
import { Btn, Card, EmptyState, Pill, formatDateTime, td, th } from '@/Components/BloodBank/ui';

const responseTone = { accepted: 'teal', declined: 'slate', pending: 'amber' };

export default function Show({ request, donors }) {
    const isOpen = request.status === 'open';
    const accepted = donors.filter((d) => d.response === 'accepted').length;

    const act = (name, confirmText) => {
        if (window.confirm(confirmText)) router.patch(route(name, request.id), {}, { preserveScroll: true });
    };

    return (
        <BloodBankLayout
            title={`Request ${request.reference}`}
            actions={
                isOpen && (
                    <div className="flex gap-2">
                        <Btn variant="danger" onClick={() => act('blood-bank.emergency.cancel', 'Cancel this request? Donors will not be notified.')}>Cancel request</Btn>
                        <Btn onClick={() => act('blood-bank.emergency.fulfill', 'Mark this request as fulfilled?')}>Mark fulfilled</Btn>
                    </div>
                )
            }
        >
            <Card>
                <p className="text-lg font-semibold text-slate-900">
                    {request.units_needed} × {request.blood_group} {request.component_label.toLowerCase()}
                    <span className="ml-3 align-middle"><Pill tone={isOpen ? 'rose' : 'slate'}>{request.status.charAt(0).toUpperCase() + request.status.slice(1)}</Pill></span>
                </p>
                <p className="mt-1 text-sm text-slate-600">
                    {request.hospital} · {request.radius_km} km radius · sent {formatDateTime(request.broadcast_at)} · replies accepted until {formatDateTime(request.expires_at)}
                </p>
                {request.note && <p className="mt-3 text-sm text-slate-700">{request.note}</p>}
                <p className="mt-3 text-sm text-slate-700">
                    <strong>{request.donors_notified_count}</strong> donor(s) alerted, <strong>{accepted}</strong> accepted.
                </p>
            </Card>

            <Card title="Donor responses" className="overflow-hidden">
                {donors.length === 0 ? (
                    <EmptyState>No donors matched this request. Widen the radius and send a new one.</EmptyState>
                ) : (
                    <div className="-m-5 overflow-x-auto">
                        <table className="min-w-full divide-y divide-slate-200">
                            <thead className="bg-slate-50">
                                <tr>
                                    <th className={th}>Donor</th>
                                    <th className={th}>Group</th>
                                    <th className={th}>Phone</th>
                                    <th className={th}>Distance</th>
                                    <th className={th}>Response</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-100">
                                {donors.map((d) => (
                                    <tr key={d.id}>
                                        <td className={`${td} font-medium text-slate-900`}>{d.name}</td>
                                        <td className={td}>{d.blood_group}</td>
                                        <td className={td}>{d.phone ?? '—'}</td>
                                        <td className={td}>{d.distance_km.toFixed(1)} km</td>
                                        <td className={td}>
                                            <Pill tone={responseTone[d.response]}>{d.response.charAt(0).toUpperCase() + d.response.slice(1)}</Pill>
                                            {d.responded_at && <span className="ml-2 text-xs text-slate-500">{formatDateTime(d.responded_at)}</span>}
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                )}
            </Card>
        </BloodBankLayout>
    );
}
