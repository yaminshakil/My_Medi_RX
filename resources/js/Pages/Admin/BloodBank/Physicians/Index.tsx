import { useEffect, useState } from 'react';
import { router, useForm } from '@inertiajs/react';
import BloodBankLayout from '@/Components/BloodBank/BloodBankLayout';
import { Btn, Card, EmptyState, Field, Pagination, Pill, clean, inputCls, td, th } from '@/Components/BloodBank/ui';
import { BreadcrumbItem } from '../../../../types';

export default function Index({ physicians, filters }) {
    const [search, setSearch] = useState(filters.search ?? '');

    const { data, setData, post, processing, errors, reset } = useForm({
        name: '', license_no: '', department: '', phone: '',
    });

    // Server-side search, same pattern as the other lists.
    useEffect(() => {
        if (search === (filters.search ?? '')) return;
        const t = setTimeout(() => {
            router.get(route('blood-bank.physicians.index'), clean({ search }), { preserveState: true, replace: true });
        }, 350);
        return () => clearTimeout(t);
    }, [search]);

    const submit = (e) => {
        e.preventDefault();
        post(route('blood-bank.physicians.store'), { preserveScroll: true, onSuccess: () => reset() });
    };

    const toggle = (p) =>
        router.patch(route('blood-bank.physicians.toggle', p.id), {}, { preserveScroll: true });

    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: 'Physicians',
            href: '/blood-bank/physicians',
        },
    ];
    return (
        <BloodBankLayout title="Physicians" breadcrumbs={breadcrumbs}>
            <form onSubmit={submit}>
                <Card title="Add physician">
                    <div className="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                        <Field label="Full name" error={errors.name}>
                            <input className={inputCls} value={data.name} onChange={(e) => setData('name', e.target.value)} required />
                        </Field>
                        <Field label="License number" error={errors.license_no}>
                            <input className={inputCls} value={data.license_no} onChange={(e) => setData('license_no', e.target.value)} />
                        </Field>
                        <Field label="Department" error={errors.department}>
                            <input className={inputCls} value={data.department} onChange={(e) => setData('department', e.target.value)} />
                        </Field>
                        <Field label="Phone" error={errors.phone}>
                            <input className={inputCls} value={data.phone} onChange={(e) => setData('phone', e.target.value)} />
                        </Field>
                    </div>
                    <div className="mt-6 flex justify-end">
                        <Btn type="submit" disabled={processing}>Add physician</Btn>
                    </div>
                </Card>
            </form>

            <Card>
                <input
                    type="search"
                    value={search}
                    onChange={(e) => setSearch(e.target.value)}
                    placeholder="Name, license or department"
                    aria-label="Search physicians"
                    className={inputCls}
                />
            </Card>

            <Card className="overflow-hidden">
                {physicians.data.length === 0 ? (
                    <EmptyState>No physicians yet. Add one above to start issuing units.</EmptyState>
                ) : (
                    <div className="-m-5 overflow-x-auto">
                        <table className="min-w-full divide-y divide-slate-200">
                            <thead className="bg-slate-50">
                                <tr>
                                    <th className={th}>Name</th>
                                    <th className={th}>License</th>
                                    <th className={th}>Department</th>
                                    <th className={th}>Phone</th>
                                    <th className={th}>Status</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-100">
                                {physicians.data.map((p) => (
                                    <tr key={p.id}>
                                        <td className={`${td} font-medium text-slate-900`}>{p.name}</td>
                                        <td className={td}>{p.license_no ?? '—'}</td>
                                        <td className={td}>{p.department ?? '—'}</td>
                                        <td className={td}>{p.phone ?? '—'}</td>
                                        <td className={td}>
                                            <Pill tone={p.is_active ? 'teal' : 'slate'}>{p.is_active ? 'Active' : 'Inactive'}</Pill>
                                            <button type="button" onClick={() => toggle(p)} className="ml-3 text-sm font-medium text-rose-800 hover:underline">
                                                {p.is_active ? 'Deactivate' : 'Activate'}
                                            </button>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                )}
            </Card>
            <Pagination links={physicians.links} />
        </BloodBankLayout>
    );
}
