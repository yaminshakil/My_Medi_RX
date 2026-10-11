import { useEffect, useState } from 'react';
import { router } from '@inertiajs/react';
import BloodBankLayout from '@/Components/BloodBank/BloodBankLayout';
import {
    Btn, Card, EmptyState, ExpiryPill, Pagination, Select, StatusPill,
    clean, formatDateTime, inputCls, td, th,
} from '@/Components/BloodBank/ui';

export default function Index({ bags, filters, options }) {
    const [search, setSearch] = useState(filters.search ?? '');

    // Filtering happens on the server (full database query), so every change re-requests the list.
    const apply = (next) =>
        router.get(route('blood-bank.bags.index'), clean({ ...filters, ...next }), {
            preserveState: true,
            replace: true,
        });

    // Debounce typing in the search box so we do not query on every keystroke.
    useEffect(() => {
        if (search === (filters.search ?? '')) return;
        const t = setTimeout(() => apply({ search }), 350);
        return () => clearTimeout(t);
    }, [search]);

    const discard = (bag) => {
        const reason = window.prompt(`Why is unit ${bag.bag_number} being discarded?`);
        if (reason) router.patch(route('blood-bank.bags.discard', bag.id), { reason }, { preserveScroll: true });
    };

    const release = (bag) => router.patch(route('blood-bank.bags.release', bag.id), {}, { preserveScroll: true });

    const canDiscard = (bag) => ['available', 'quarantined', 'expired'].includes(bag.status);

    return (
        <BloodBankLayout title="Blood units" actions={<Btn href={route('blood-bank.bags.create')}>Register unit</Btn>}>
            <Card>
                <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-6">
                    <input
                        type="search"
                        value={search}
                        onChange={(e) => setSearch(e.target.value)}
                        placeholder="Bag or batch number"
                        aria-label="Search by bag or batch number"
                        className={`${inputCls} lg:col-span-2`}
                    />
                    <Select aria-label="Blood group" value={filters.blood_group ?? ''} onChange={(e) => apply({ blood_group: e.target.value })} options={options.groups} placeholder="All groups" />
                    <Select aria-label="Component" value={filters.component ?? ''} onChange={(e) => apply({ component: e.target.value })} options={options.components} placeholder="All components" />
                    <Select aria-label="Status" value={filters.status ?? ''} onChange={(e) => apply({ status: e.target.value })} options={options.statuses} placeholder="Any status" />
                    <Select
                        aria-label="Expiry"
                        value={filters.expiry ?? ''}
                        onChange={(e) => apply({ expiry: e.target.value })}
                        options={[{ value: 'expiring', label: 'Expiring soon' }, { value: 'expired', label: 'Past expiry' }]}
                        placeholder="Any expiry"
                    />
                </div>
            </Card>

            <Card className="overflow-hidden">
                {bags.data.length === 0 ? (
                    <EmptyState>No units match these filters.</EmptyState>
                ) : (
                    <div className="-m-5 overflow-x-auto">
                        <table className="min-w-full divide-y divide-slate-200">
                            <thead className="bg-slate-50">
                                <tr>
                                    <th className={th}>Bag / batch</th>
                                    <th className={th}>Group</th>
                                    <th className={th}>Component</th>
                                    <th className={th}>Volume</th>
                                    <th className={th}>Collected</th>
                                    <th className={th}>Expires</th>
                                    <th className={th}>Storage</th>
                                    <th className={th}>Status</th>
                                    <th className={th}><span className="sr-only">Actions</span></th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-100">
                                {bags.data.map((bag) => (
                                    <tr key={bag.id}>
                                        <td className={td}>
                                            <p className="font-medium text-slate-900">{bag.bag_number}</p>
                                            <p className="text-xs text-slate-500">Batch {bag.batch_number}</p>
                                        </td>
                                        <td className={`${td} font-semibold text-slate-900`}>{bag.blood_group}</td>
                                        <td className={td}>{bag.component_label}</td>
                                        <td className={td}>{bag.volume_ml} ml</td>
                                        <td className={td}>{formatDateTime(bag.collected_at)}</td>
                                        <td className={td}>
                                            <p>{formatDateTime(bag.expires_at)}</p>
                                            {['available', 'quarantined'].includes(bag.status) && (
                                                <ExpiryPill state={bag.expiry_state} hours={bag.hours_left} />
                                            )}
                                        </td>
                                        <td className={td}>{bag.storage_unit}</td>
                                        <td className={td}><StatusPill status={bag.status} /></td>
                                        <td className={`${td} whitespace-nowrap text-right`}>
                                            {bag.status === 'quarantined' && (
                                                <button type="button" onClick={() => release(bag)} className="mr-3 font-medium text-teal-700 hover:underline">
                                                    Release
                                                </button>
                                            )}
                                            {canDiscard(bag) && (
                                                <button type="button" onClick={() => discard(bag)} className="font-medium text-rose-700 hover:underline">
                                                    Discard
                                                </button>
                                            )}
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                )}
            </Card>

            <div className="flex items-center justify-between">
                <p className="text-sm text-slate-500">
                    {bags.total > 0 ? `Showing ${bags.from}–${bags.to} of ${bags.total}` : ''}
                </p>
                <Pagination links={bags.links} />
            </div>
        </BloodBankLayout>
    );
}
