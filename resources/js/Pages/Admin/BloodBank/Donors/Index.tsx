import { useEffect, useState } from 'react';
import { router, useForm } from '@inertiajs/react';
import BloodBankLayout from '@/Components/BloodBank/BloodBankLayout';
import { Btn, Card, EmptyState, Field, Pagination, Pill, Select, clean, inputCls, td, th } from '@/Components/BloodBank/ui';

export default function Index({ donors, filters, groups }) {
    const [search, setSearch] = useState(filters.search ?? '');
    const [adding, setAdding] = useState(false);

    const { data, setData, post, processing, errors, reset } = useForm({
        name: '', phone: '', email: '', blood_group: '', latitude: '', longitude: '', city: '', last_donated_at: '',
    });

    // Server-side filtering, same pattern as the other lists.
    const apply = (next) =>
        router.get(route('blood-bank.donors.index'), clean({ ...filters, ...next }), { preserveState: true, replace: true });

    useEffect(() => {
        if (search === (filters.search ?? '')) return;
        const t = setTimeout(() => apply({ search }), 350);
        return () => clearTimeout(t);
    }, [search]);

    // Fill coordinates from the browser so staff do not have to look them up.
    const useMyLocation = () => {
        navigator.geolocation?.getCurrentPosition((pos) => {
            setData((d) => ({ ...d, latitude: pos.coords.latitude.toFixed(6), longitude: pos.coords.longitude.toFixed(6) }));
        });
    };

    const submit = (e) => {
        e.preventDefault();
        post(route('blood-bank.donors.store'), { preserveScroll: true, onSuccess: () => { reset(); setAdding(false); } });
    };

    const toggleVerified = (donor) =>
        router.patch(route('blood-bank.donors.verify', donor.id), { verified: !donor.is_verified }, { preserveScroll: true });

    return (
        <BloodBankLayout title="Donors" actions={<Btn onClick={() => setAdding((v) => !v)}>{adding ? 'Close form' : 'Add donor'}</Btn>}>
            {adding && (
                <form onSubmit={submit}>
                    <Card title="New donor">
                        <div className="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                            <Field label="Full name" error={errors.name}>
                                <input className={inputCls} value={data.name} onChange={(e) => setData('name', e.target.value)} required />
                            </Field>
                            <Field label="Blood group" error={errors.blood_group}>
                                <Select value={data.blood_group} onChange={(e) => setData('blood_group', e.target.value)} options={groups} placeholder="Select group" required />
                            </Field>
                            <Field label="Last donated" error={errors.last_donated_at}>
                                <input type="date" className={inputCls} value={data.last_donated_at} onChange={(e) => setData('last_donated_at', e.target.value)} />
                            </Field>
                            <Field label="Phone" error={errors.phone}>
                                <input className={inputCls} value={data.phone} onChange={(e) => setData('phone', e.target.value)} />
                            </Field>
                            <Field label="Email" error={errors.email} hint="Emergency alerts are sent here">
                                <input type="email" className={inputCls} value={data.email} onChange={(e) => setData('email', e.target.value)} />
                            </Field>
                            <Field label="City" error={errors.city}>
                                <input className={inputCls} value={data.city} onChange={(e) => setData('city', e.target.value)} />
                            </Field>
                            <Field label="Latitude" error={errors.latitude}>
                                <input type="number" step="any" className={inputCls} value={data.latitude} onChange={(e) => setData('latitude', e.target.value)} required />
                            </Field>
                            <Field label="Longitude" error={errors.longitude}>
                                <input type="number" step="any" className={inputCls} value={data.longitude} onChange={(e) => setData('longitude', e.target.value)} required />
                            </Field>
                            <div className="flex items-end">
                                <Btn variant="secondary" onClick={useMyLocation}>Use my current location</Btn>
                            </div>
                        </div>
                        <div className="mt-6 flex justify-end">
                            <Btn type="submit" disabled={processing}>Save donor</Btn>
                        </div>
                    </Card>
                </form>
            )}

            <Card>
                <div className="grid gap-3 sm:grid-cols-3">
                    <input type="search" value={search} onChange={(e) => setSearch(e.target.value)} placeholder="Name, phone or email" aria-label="Search donors" className={inputCls} />
                    <Select aria-label="Blood group" value={filters.blood_group ?? ''} onChange={(e) => apply({ blood_group: e.target.value })} options={groups} placeholder="All groups" />
                    <Select
                        aria-label="Verification"
                        value={filters.verified ?? ''}
                        onChange={(e) => apply({ verified: e.target.value })}
                        options={[{ value: '1', label: 'Verified' }, { value: '0', label: 'Not verified' }]}
                        placeholder="Any verification"
                    />
                </div>
            </Card>

            <Card className="overflow-hidden">
                {donors.data.length === 0 ? (
                    <EmptyState>No donors match these filters.</EmptyState>
                ) : (
                    <div className="-m-5 overflow-x-auto">
                        <table className="min-w-full divide-y divide-slate-200">
                            <thead className="bg-slate-50">
                                <tr>
                                    <th className={th}>Donor</th>
                                    <th className={th}>Group</th>
                                    <th className={th}>Contact</th>
                                    <th className={th}>City</th>
                                    <th className={th}>Last donated</th>
                                    <th className={th}>Verification</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-100">
                                {donors.data.map((d) => (
                                    <tr key={d.id}>
                                        <td className={`${td} font-medium text-slate-900`}>{d.name}</td>
                                        <td className={`${td} font-semibold`}>{d.blood_group}</td>
                                        <td className={td}>{d.phone ?? '—'}<span className="block text-xs text-slate-500">{d.email}</span></td>
                                        <td className={td}>{d.city ?? '—'}</td>
                                        <td className={td}>{d.last_donated_at ?? 'Never'}</td>
                                        <td className={td}>
                                            <Pill tone={d.is_verified ? 'teal' : 'amber'}>{d.is_verified ? 'Verified' : 'Pending'}</Pill>
                                            <button type="button" onClick={() => toggleVerified(d)} className="ml-3 text-sm font-medium text-rose-800 hover:underline">
                                                {d.is_verified ? 'Remove' : 'Verify'}
                                            </button>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                )}
            </Card>
            <Pagination links={donors.links} />
        </BloodBankLayout>
    );
}
