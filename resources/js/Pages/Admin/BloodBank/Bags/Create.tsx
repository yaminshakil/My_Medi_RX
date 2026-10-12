import { useForm } from '@inertiajs/react';
import BloodBankLayout from '@/Components/BloodBank/BloodBankLayout';
import { Btn, Card, Field, Select, formatDateTime, inputCls } from '@/Components/BloodBank/ui';

export default function Create({ options }) {
    const { data, setData, post, processing, errors } = useForm({
        bag_number: '',
        batch_number: '',
        blood_group: '',
        component: '',
        volume_ml: '',
        collected_at: '',
        expires_at: '',
        storage_unit_id: '',
        quarantine: false,
    });

    // Preview the expiry the server will apply when the field is left empty.
    const component = options.components.find((c) => c.value === data.component);
    const suggestedExpiry =
        component && data.collected_at && !data.expires_at
            ? new Date(new Date(data.collected_at).getTime() + component.shelf_life_days * 86400000).toISOString()
            : null;

    const submit = (e) => {
        e.preventDefault();
        post(route('blood-bank.bags.store'));
    };

    return (
        <BloodBankLayout title="Register blood unit">
            <form onSubmit={submit}>
                <Card>
                    <div className="grid gap-5 sm:grid-cols-2">
                        <Field label="Bag number" error={errors.bag_number} hint="Barcode or ISBT number printed on the bag">
                            <input className={inputCls} value={data.bag_number} onChange={(e) => setData('bag_number', e.target.value)} required />
                        </Field>
                        <Field label="Batch number" error={errors.batch_number}>
                            <input className={inputCls} value={data.batch_number} onChange={(e) => setData('batch_number', e.target.value)} required />
                        </Field>
                        <Field label="Blood group" error={errors.blood_group}>
                            <Select value={data.blood_group} onChange={(e) => setData('blood_group', e.target.value)} options={options.groups} placeholder="Select group" required />
                        </Field>
                        <Field label="Component" error={errors.component}>
                            <Select value={data.component} onChange={(e) => setData('component', e.target.value)} options={options.components} placeholder="Select component" required />
                        </Field>
                        <Field label="Volume (ml)" error={errors.volume_ml}>
                            <input type="number" min="10" max="1000" className={inputCls} value={data.volume_ml} onChange={(e) => setData('volume_ml', e.target.value)} required />
                        </Field>
                        <Field label="Storage unit" error={errors.storage_unit_id}>
                            <Select value={data.storage_unit_id} onChange={(e) => setData('storage_unit_id', e.target.value)} options={options.storage_units} placeholder="Select storage" required />
                        </Field>
                        <Field label="Collected at" error={errors.collected_at}>
                            <input type="datetime-local" className={inputCls} value={data.collected_at} onChange={(e) => setData('collected_at', e.target.value)} required />
                        </Field>
                        <Field
                            label="Expires at"
                            error={errors.expires_at}
                            hint={suggestedExpiry ? `Leave empty to use the standard shelf life: ${formatDateTime(suggestedExpiry)}` : 'Leave empty to use the standard shelf life for the component'}
                        >
                            <input type="datetime-local" className={inputCls} value={data.expires_at} onChange={(e) => setData('expires_at', e.target.value)} />
                        </Field>
                    </div>

                    <label className="mt-5 flex items-start gap-2 text-sm text-slate-700">
                        <input
                            type="checkbox"
                            checked={data.quarantine}
                            onChange={(e) => setData('quarantine', e.target.checked)}
                            className="mt-0.5 rounded border-slate-300 text-rose-800 focus:ring-rose-700"
                        />
                        <span>
                            Hold in quarantine until screening results are back
                            <span className="block text-xs text-slate-500">Quarantined units are not counted as stock and cannot be issued.</span>
                        </span>
                    </label>

                    <div className="mt-6 flex justify-end gap-2">
                        <Btn variant="secondary" href={route('blood-bank.bags.index')}>Cancel</Btn>
                        <Btn type="submit" disabled={processing}>Register unit</Btn>
                    </div>
                </Card>
            </form>
        </BloodBankLayout>
    );
}
