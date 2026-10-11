import { useEffect } from 'react';
import { Link, router } from '@inertiajs/react';
import BloodBankLayout from '@/Components/BloodBank/BloodBankLayout';
import StockMatrix from '@/Components/BloodBank/StockMatrix';
import { Btn, Card, EmptyState, ExpiryPill, formatDateTime } from '@/Components/BloodBank/ui';

export default function Dashboard({ stock, expiring, summary, recentIssues, emergencyDefaults }) {
    // Live stock: reload only the stock props whenever the server announces a change (Echo),
    // with a 60s poll as a fallback for installs that have no websocket server.
    useEffect(() => {
        const reload = () =>
            router.reload({ only: ['stock', 'expiring', 'summary', 'recentIssues'], preserveScroll: true });

        const timer = setInterval(reload, 60000);
        if (window.Echo) {
            window.Echo.private('blood-bank.stock').listen('.stock.updated', reload);
        }

        return () => {
            clearInterval(timer);
            if (window.Echo) window.Echo.leave('blood-bank.stock');
        };
    }, []);

    // One-click broadcast from a critical cell: confirm once, then post with sensible defaults.
    const requestDonors = (group, component) => {
        if (!window.confirm(`Send an urgent request for ${group} ${component.label.toLowerCase()} to verified donors nearby?`)) {
            return;
        }
        router.post(route('blood-bank.emergency.store'), {
            blood_group: group,
            component: component.value,
            units_needed: 2,
            urgency: 'urgent',
            radius_km: emergencyDefaults.radius_km,
        });
    };

    const figures = [
        ['Units available', summary.available_units],
        ['Expiring soon', summary.expiring_count],
        ['Open emergency requests', summary.open_requests],
        ['Issued today', summary.issued_today],
    ];

    return (
        <BloodBankLayout
            title="Blood stock"
            actions={
                <div className="flex gap-2">
                    <Btn variant="secondary" href={route('blood-bank.bags.create')}>
                        Register unit
                    </Btn>
                    <Btn href={route('blood-bank.issues.create')}>Issue a unit</Btn>
                </div>
            }
        >
            <dl className="grid grid-cols-2 divide-x divide-slate-200 rounded-lg bg-white shadow-sm ring-1 ring-slate-200 lg:grid-cols-4">
                {figures.map(([label, value]) => (
                    <div key={label} className="px-5 py-4">
                        <dt className="text-sm text-slate-500">{label}</dt>
                        <dd className="mt-1 text-3xl font-semibold tabular-nums text-slate-900">{value}</dd>
                    </div>
                ))}
            </dl>

            <Card title="Stock by blood group and component">
                <StockMatrix stock={stock} onRequest={requestDonors} />
            </Card>

            <div className="grid gap-6 lg:grid-cols-2">
                <Card
                    title="Expiring soon"
                    actions={
                        <Link href={route('blood-bank.bags.index', { expiry: 'expiring' })} className="text-sm font-medium text-rose-800 hover:underline">
                            View all
                        </Link>
                    }
                >
                    {expiring.length === 0 ? (
                        <EmptyState>No units are close to expiry.</EmptyState>
                    ) : (
                        <ul className="divide-y divide-slate-100">
                            {expiring.map((bag) => (
                                <li key={bag.id} className="flex items-center justify-between gap-3 py-2.5">
                                    <div>
                                        <p className="text-sm font-medium text-slate-900">
                                            {bag.blood_group} · {bag.component_label}
                                        </p>
                                        <p className="text-xs text-slate-500">
                                            Bag {bag.bag_number} · batch {bag.batch_number} · {bag.storage_unit}
                                        </p>
                                    </div>
                                    <ExpiryPill state={bag.expiry_state} hours={bag.hours_left} />
                                </li>
                            ))}
                        </ul>
                    )}
                </Card>

                <Card
                    title="Recently issued"
                    actions={
                        <Link href={route('blood-bank.issues.index')} className="text-sm font-medium text-rose-800 hover:underline">
                            Open issue log
                        </Link>
                    }
                >
                    {recentIssues.length === 0 ? (
                        <EmptyState>Nothing has been issued yet.</EmptyState>
                    ) : (
                        <ul className="divide-y divide-slate-100">
                            {recentIssues.map((issue) => (
                                <li key={issue.id} className="py-2.5">
                                    <Link href={route('blood-bank.issues.show', issue.id)} className="block hover:bg-slate-50">
                                        <p className="text-sm font-medium text-slate-900">
                                            {issue.bag.blood_group} {issue.bag.component_label} to {issue.patient.name}
                                        </p>
                                        <p className="text-xs text-slate-500">
                                            {issue.physician.name} · {formatDateTime(issue.issued_at)}
                                        </p>
                                    </Link>
                                </li>
                            ))}
                        </ul>
                    )}
                </Card>
            </div>
        </BloodBankLayout>
    );
}
