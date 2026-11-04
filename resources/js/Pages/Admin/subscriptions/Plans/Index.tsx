
import { Head, useForm } from '@inertiajs/react';

interface Props {
    plans: {
        data: any[];
    };
}

export default function Index({ plans }: Props) {
    const { data, setData, post, processing, errors } = useForm({
        name: '',
        description: '',
        price: '299',
        billing_cycle: 'monthly',
        duration_days: '30',
        user_type: 'Patient',
        features: {
            ai_assistant: { enabled: true, limit: 5 },
            prescription_qr: { enabled: false },
            advanced_reports: { enabled: false },
        },
        is_active: true,
    });

    const submit = (e: React.FormEvent) => {
        e.preventDefault();

        post(route('admin.subscription-plans.store'));
    };

    return (
        <>
            <Head title="Subscription Plans" />

            <div className="space-y-6 p-6">
                <h1 className="text-2xl font-bold">
                    Subscription Plans
                </h1>

                <form onSubmit={submit}
                    className="max-w-2xl space-y-4 rounded-xl border p-6">

                    <div>
                        <label>Plan Name</label>
                        <input
                            className="w-full rounded-md border p-2"
                            value={data.name}
                            onChange={e => setData('name', e.target.value)}
                            required
                        />
                        {errors.name && <p className="text-red-500">{errors.name}</p>}
                    </div>

                    <div>
                        <label>Description</label>
                        <textarea
                            className="w-full rounded-md border p-2"
                            value={data.description}
                            onChange={e => setData('description', e.target.value)}
                        />
                    </div>

                    <div>
                        <label>Price (BDT)</label>
                        <input
                            type="number"
                            className="w-full rounded-md border p-2"
                            value={data.price}
                            onChange={e => setData('price', e.target.value)}
                            required
                        />
                    </div>

                    <div>
                        <label>User Type</label>
                        <select
                            className="w-full rounded-md border p-2"
                            value={data.user_type}
                            onChange={e => setData('user_type', e.target.value)}
                        >
                            {['All', 'Patient', 'Doctor', 'Pharmacy', 'Hospital'].map(type => (
                                <option key={type} value={type}>{type}</option>
                            ))}
                        </select>
                    </div>

                    <div>
                        <label>Billing Cycle</label>
                        <select
                            className="w-full rounded-md border p-2"
                            value={data.billing_cycle}
                            onChange={e => {
                                const cycle = e.target.value;
                                setData('billing_cycle', cycle);

                                const days: Record<string, string> = {
                                    monthly: '30',
                                    quarterly: '90',
                                    yearly: '365',
                                    custom: data.duration_days,
                                };

                                setData('duration_days', days[cycle]);
                            }}
                        >
                            <option value="monthly">Monthly</option>
                            <option value="quarterly">Quarterly</option>
                            <option value="yearly">Yearly</option>
                            <option value="custom">Custom</option>
                        </select>
                    </div>

                    <div>
                        <label>Duration (Days)</label>
                        <input
                            type="number"
                            className="w-full rounded-md border p-2"
                            value={data.duration_days}
                            onChange={e => setData('duration_days', e.target.value)}
                            min="1"
                        />
                    </div>

                    <button
                        type="submit"
                        disabled={processing}
                        className="rounded-md bg-primary px-5 py-2 text-white"
                    >
                        {processing ? 'Creating...' : 'Create Subscription Plan'}
                    </button>
                </form>

                <h2 className="text-xl font-semibold">Existing Plans</h2>

                <div className="space-y-2">
                    {plans.data.map(plan => (
                        <div key={plan.id}
                            className="flex justify-between rounded-lg border p-4">
                            <div>
                                <strong>{plan.name}</strong>
                                <p>{plan.user_type} · ৳{plan.price} · {plan.billing_cycle}</p>
                            </div>
                            <span>{plan.is_active ? 'Active' : 'Inactive'}</span>
                        </div>
                    ))}
                </div>
            </div>
        </>
    );
}
