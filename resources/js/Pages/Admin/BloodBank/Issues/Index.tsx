import { useEffect, useState } from 'react';
import { Link, router } from '@inertiajs/react';
import BloodBankLayout from '@/Components/BloodBank/BloodBankLayout';
import { Btn, Card, EmptyState, Pagination, Pill, Select, clean, formatDateTime, inputCls, td, th } from '@/Components/BloodBank/ui';

export default function Index({ issues, filters, components, physicians }) {
    const [search, setSearch] = useState(filters.search ?? '');

    // Server-side filtering: each change re-queries the log.
    const apply = (next) =>
        router.get(route('blood-bank.issues.index'), clean({ ...filters, ...next }), { preserveState: true, replace: true });

    useEffect(() => {
        if (search === (filters.search ?? '')) return;
        const t = setTimeout(() => apply({ search }), 350);
        return () => clearTimeout(t);
    }, [search]);

    return (
        <BloodBankLayout title="Transfusion and issue log" actions={<Btn href={route('blood-bank.issues.create')}>Issue a unit</Btn>}>
            <Card>
                <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
                    <input
                        type="search"
                        value={search}
                        onChange={(e) => setSearch(e.target.value)}
                        placeholder="Patient name, MRN, file no., bag or issue no."
                        aria-label="Search the issue log"
                        className={`${inputCls} lg:col-span-2`}
                    />
                    <Select aria-label="Component" value={filters.component ?? ''} onChange={(e) => apply({ component: e.target.value })} options={components} placeholder="All components" />
                    <Select aria-label="Physician" value={filters.physician_id ?? ''} onChange={(e) => apply({ physician_id: e.target.value })} options={physicians} placeholder="All physicians" />
                    <div className="flex gap-2">
                        <input type="date" aria-label="From date" value={filters.from ?? ''} onChange={(e) => apply({ from: e.target.value })} className={inputCls} />
                        <input type="date" aria-label="To date" value={filters.to ?? ''} onChange={(e) => apply({ to: e.target.value })} className={inputCls} />
                    </div>
                </div>
            </Card>

            <Card className="overflow-hidden">
                {issues.data.length === 0 ? (
                    <EmptyState>No issue records match these filters.</EmptyState>
                ) : (
                    <div className="-m-5 overflow-x-auto">
                        <table className="min-w-full divide-y divide-slate-200">
                            <thead className="bg-slate-50">
                                <tr>
                                    <th className={th}>Issue no.</th>
                                    <th className={th}>Issued</th>
                                    <th className={th}>Unit</th>
                                    <th className={th}>Patient</th>
                                    <th className={th}>Attending physician</th>
                                    <th className={th}>Outcome</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-100">
                                {issues.data.map((issue) => (
                                    <tr key={issue.id} className="hover:bg-slate-50">
                                        <td className={td}>
                                            <Link href={route('blood-bank.issues.show', issue.id)} className="font-medium text-rose-800 hover:underline">
                                                {issue.issue_number}
                                            </Link>
                                        </td>
                                        <td className={td}>{formatDateTime(issue.issued_at)}</td>
                                        <td className={td}>
                                            <p className="font-medium text-slate-900">{issue.bag.blood_group} · {issue.bag.component_label}</p>
                                            <p className="text-xs text-slate-500">Bag {issue.bag.bag_number}</p>
                                        </td>
                                        <td className={td}>
                                            <p className="font-medium text-slate-900">{issue.patient.name}</p>
                                            <p className="text-xs text-slate-500">MRN {issue.patient.mrn}{issue.patient.clinical_file_no ? ` · File ${issue.patient.clinical_file_no}` : ''}</p>
                                        </td>
                                        <td className={td}>{issue.physician.name}</td>
                                        <td className={td}>
                                            {issue.reaction_noted ? <Pill tone="rose">Reaction noted</Pill>
                                                : issue.transfusion_completed_at ? <Pill tone="teal">Completed</Pill>
                                                : <Pill tone="amber">Awaiting outcome</Pill>}
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                )}
            </Card>

            <div className="flex items-center justify-between">
                <p className="text-sm text-slate-500">{issues.total > 0 ? `Showing ${issues.from}–${issues.to} of ${issues.total}` : ''}</p>
                <Pagination links={issues.links} />
            </div>
        </BloodBankLayout>
    );
}
