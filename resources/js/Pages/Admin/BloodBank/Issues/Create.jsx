import { useState } from 'react';
import { router, useForm } from '@inertiajs/react';
import BloodBankLayout from '@/Components/BloodBank/BloodBankLayout';
import { Btn, Card, ExpiryPill, Field, Pill, Select, clean, cx, formatDateTime, inputCls } from '@/Components/BloodBank/ui';

export default function Create({ filters, patients, patient, availableBags, physicians, components }) {
    const [term, setTerm] = useState(filters.patient_search ?? '');

    const { data, setData, post, processing, errors } = useForm({
        patient_id: patient?.id ?? '',
        component: filters.component ?? '',
        blood_bag_id: '',
        physician_id: '',
        indication: '',
        ward: '',
        compatibility_override_reason: '',
    });

    // The server returns patients and matching units through partial reloads of this same page.
    // Form state survives because of preserveState.
    const refresh = (extra = {}) =>
        router.get(
            route('blood-bank.issues.create'),
            clean({
                patient_search: term,
                patient_id: data.patient_id,
                component: data.component,
                include_all: filters.include_all,
                ...extra,
            }),
            {
                preserveState: true,
                preserveScroll: true,
                replace: true,
                only: ['filters', 'patients', 'patient', 'availableBags'],
            }
        );

    const choosePatient = (p) => {
        setData((d) => ({ ...d, patient_id: p.id, blood_bag_id: '' }));
        refresh({ patient_id: p.id });
    };

    const chooseComponent = (value) => {
        setData((d) => ({ ...d, component: value, blood_bag_id: '' }));
        refresh({ component: value });
    };

    const selectedBag = availableBags.find((b) => String(b.id) === String(data.blood_bag_id));
    const needsOverride = selectedBag && !selectedBag.compatible;

    const submit = (e) => {
        e.preventDefault();
        post(route('blood-bank.issues.store'));
    };

    return (
        <BloodBankLayout title="Issue a unit">
            <form onSubmit={submit} className="space-y-6">
                <Card title="1. Recipient">
                    <div className="flex gap-2">
                        <input
                            type="search"
                            value={term}
                            onChange={(e) => setTerm(e.target.value)}
                            onKeyDown={(e) => {
                                if (e.key === 'Enter') {
                                    e.preventDefault();
                                    refresh({ patient_search: term });
                                }
                            }}
                            placeholder="Search by name, MRN or clinical file number"
                            aria-label="Search patients"
                            className={inputCls}
                        />
                        <Btn variant="secondary" onClick={() => refresh({ patient_search: term })}>Search</Btn>
                    </div>

                    {patients.length > 0 && (
                        <ul className="mt-3 divide-y divide-slate-100 rounded-md ring-1 ring-slate-200">
                            {patients.map((p) => (
                                <li key={p.id} className="flex items-center justify-between gap-3 px-3 py-2">
                                    <div>
                                        <p className="text-sm font-medium text-slate-900">{p.name}</p>
                                        <p className="text-xs text-slate-500">MRN {p.mrn}{p.clinical_file_no ? ` · File ${p.clinical_file_no}` : ''} · {p.blood_group ?? 'Group not recorded'}</p>
                                    </div>
                                    <Btn variant="secondary" onClick={() => choosePatient(p)}>Select</Btn>
                                </li>
                            ))}
                        </ul>
                    )}

                    {patient && (
                        <div className="mt-3 rounded-md bg-slate-50 px-4 py-3 ring-1 ring-slate-200">
                            <p className="text-sm font-semibold text-slate-900">{patient.name}</p>
                            <p className="text-xs text-slate-600">
                                MRN {patient.mrn}{patient.clinical_file_no ? ` · Clinical file ${patient.clinical_file_no}` : ''} · Blood group {patient.blood_group ?? 'not recorded'}
                            </p>
                        </div>
                    )}
                    {errors.patient_id && <p className="mt-2 text-xs text-rose-700">Select a patient to continue.</p>}
                </Card>

                <Card title="2. Unit">
                    <Field label="Component" error={errors.component}>
                        <Select value={data.component} onChange={(e) => chooseComponent(e.target.value)} options={components} placeholder="Select component" />
                    </Field>

                    {data.component && (
                        <div className="mt-4">
                            <label className="mb-2 flex items-center gap-2 text-sm text-slate-600">
                                <input
                                    type="checkbox"
                                    checked={!!filters.include_all}
                                    onChange={(e) => refresh({ include_all: e.target.checked ? 1 : '' })}
                                    className="rounded border-slate-300 text-rose-800 focus:ring-rose-700"
                                />
                                Also show units outside the compatibility rules (needs an override reason)
                            </label>

                            {availableBags.length === 0 ? (
                                <p className="rounded-md bg-amber-50 px-4 py-3 text-sm text-amber-900 ring-1 ring-amber-200">
                                    No usable units for this component{patient ? ` that suit ${patient.blood_group ?? 'this patient'}` : ''}. Consider sending an emergency request.
                                </p>
                            ) : (
                                <fieldset>
                                    <legend className="sr-only">Available units, soonest expiry first</legend>
                                    <ul className="max-h-80 divide-y divide-slate-100 overflow-y-auto rounded-md ring-1 ring-slate-200">
                                        {availableBags.map((bag) => (
                                            <li key={bag.id}>
                                                <label className={cx('flex cursor-pointer items-center gap-3 px-3 py-2.5 hover:bg-slate-50', String(data.blood_bag_id) === String(bag.id) && 'bg-rose-50')}>
                                                    <input
                                                        type="radio"
                                                        name="blood_bag_id"
                                                        value={bag.id}
                                                        checked={String(data.blood_bag_id) === String(bag.id)}
                                                        onChange={() => setData('blood_bag_id', bag.id)}
                                                        className="border-slate-300 text-rose-800 focus:ring-rose-700"
                                                    />
                                                    <span className="w-10 text-sm font-semibold text-slate-900">{bag.blood_group}</span>
                                                    <span className="flex-1 text-sm text-slate-700">
                                                        Bag {bag.bag_number}
                                                        <span className="block text-xs text-slate-500">Batch {bag.batch_number} · expires {formatDateTime(bag.expires_at)}</span>
                                                    </span>
                                                    <ExpiryPill state={bag.hours_left <= 48 ? 'critical' : 'ok'} hours={bag.hours_left} />
                                                    {!bag.compatible && <Pill tone="amber">Outside rules</Pill>}
                                                </label>
                                            </li>
                                        ))}
                                    </ul>
                                </fieldset>
                            )}
                            {errors.blood_bag_id && <p className="mt-2 text-xs text-rose-700">Pick a unit to issue.</p>}
                        </div>
                    )}

                    {needsOverride && (
                        <Field
                            className="mt-4"
                            label="Override reason"
                            error={errors.compatibility_override_reason}
                            hint="Stored permanently on the issue record."
                        >
                            <textarea
                                rows={2}
                                className={inputCls}
                                value={data.compatibility_override_reason}
                                onChange={(e) => setData('compatibility_override_reason', e.target.value)}
                                required
                            />
                        </Field>
                    )}
                </Card>

                <Card title="3. Clinical details">
                    <div className="grid gap-5 sm:grid-cols-2">
                        <Field label="Attending physician" error={errors.physician_id}>
                            <Select value={data.physician_id} onChange={(e) => setData('physician_id', e.target.value)} options={physicians} placeholder="Select physician" required />
                        </Field>
                        <Field label="Ward / bed" error={errors.ward}>
                            <input className={inputCls} value={data.ward} onChange={(e) => setData('ward', e.target.value)} />
                        </Field>
                        <Field label="Clinical indication" error={errors.indication} className="sm:col-span-2">
                            <textarea rows={3} className={inputCls} value={data.indication} onChange={(e) => setData('indication', e.target.value)} required />
                        </Field>
                    </div>

                    <div className="mt-6 flex justify-end gap-2">
                        <Btn variant="secondary" href={route('blood-bank.issues.index')}>Cancel</Btn>
                        <Btn type="submit" disabled={processing || !data.patient_id || !data.blood_bag_id}>Issue unit</Btn>
                    </div>
                </Card>
            </form>
        </BloodBankLayout>
    );
}
