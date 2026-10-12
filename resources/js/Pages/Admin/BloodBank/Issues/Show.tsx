import { useForm } from '@inertiajs/react';
import BloodBankLayout from '@/Components/BloodBank/BloodBankLayout';
import { Btn, Card, Field, Pill, formatDateTime, inputCls } from '@/Components/BloodBank/ui';

const Row = ({ label, children }) => (
    <div className="grid grid-cols-3 gap-3 py-2">
        <dt className="text-sm text-slate-500">{label}</dt>
        <dd className="col-span-2 text-sm text-slate-900">{children ?? '—'}</dd>
    </div>
);

export default function Show({ issue }) {
    const { data, setData, patch, processing, errors } = useForm({
        transfusion_started_at: issue.transfusion_started_at ?? '',
        transfusion_completed_at: issue.transfusion_completed_at ?? '',
        reaction_noted: issue.reaction_noted,
        reaction_notes: issue.reaction_notes ?? '',
    });

    const submit = (e) => {
        e.preventDefault();
        patch(route('blood-bank.issues.outcome', issue.id), { preserveScroll: true });
    };

    return (
        <BloodBankLayout
            title={`Issue ${issue.issue_number}`}
            actions={<Btn variant="secondary" href={route('blood-bank.issues.index')}>Back to log</Btn>}
        >
            <div className="grid gap-6 lg:grid-cols-2">
                <Card title="Unit issued">
                    <dl className="divide-y divide-slate-100">
                        <Row label="Unit">{issue.bag.blood_group} · {issue.bag.component_label}</Row>
                        <Row label="Bag / batch">{issue.bag.bag_number} / {issue.bag.batch_number}</Row>
                        <Row label="Stored in">{issue.bag.storage_unit}</Row>
                        <Row label="Issued">{formatDateTime(issue.issued_at)} by {issue.issued_by}</Row>
                        <Row label="Indication">{issue.indication}</Row>
                        <Row label="Ward / bed">{issue.ward}</Row>
                        {issue.compatibility_override_reason && (
                            <Row label="Override reason"><Pill tone="amber">Override</Pill> {issue.compatibility_override_reason}</Row>
                        )}
                    </dl>
                </Card>

                <Card title="Recipient and physician">
                    <dl className="divide-y divide-slate-100">
                        <Row label="Patient">{issue.patient.name}</Row>
                        <Row label="MRN">{issue.patient.mrn}</Row>
                        <Row label="Clinical file">{issue.patient.clinical_file_no}</Row>
                        <Row label="Blood group">{issue.patient.blood_group}</Row>
                        <Row label="Attending physician">{issue.physician.name}{issue.physician.department ? ` (${issue.physician.department})` : ''}</Row>
                    </dl>
                </Card>
            </div>

            <form onSubmit={submit}>
                <Card title="Transfusion outcome">
                    <div className="grid gap-5 sm:grid-cols-2">
                        <Field label="Transfusion started" error={errors.transfusion_started_at}>
                            <input type="datetime-local" className={inputCls} value={data.transfusion_started_at} onChange={(e) => setData('transfusion_started_at', e.target.value)} />
                        </Field>
                        <Field label="Transfusion completed" error={errors.transfusion_completed_at}>
                            <input type="datetime-local" className={inputCls} value={data.transfusion_completed_at} onChange={(e) => setData('transfusion_completed_at', e.target.value)} />
                        </Field>
                    </div>

                    <label className="mt-5 flex items-center gap-2 text-sm text-slate-700">
                        <input
                            type="checkbox"
                            checked={data.reaction_noted}
                            onChange={(e) => setData('reaction_noted', e.target.checked)}
                            className="rounded border-slate-300 text-rose-800 focus:ring-rose-700"
                        />
                        A transfusion reaction was noted
                    </label>

                    {data.reaction_noted && (
                        <Field className="mt-4" label="Reaction details" error={errors.reaction_notes}>
                            <textarea rows={3} className={inputCls} value={data.reaction_notes} onChange={(e) => setData('reaction_notes', e.target.value)} required />
                        </Field>
                    )}

                    <div className="mt-6 flex justify-end">
                        <Btn type="submit" disabled={processing}>Save outcome</Btn>
                    </div>
                </Card>
            </form>
        </BloodBankLayout>
    );
}
