import Input from '@/Components/Input';
import InputError from '@/Components/InputError';
import Select from '@/Components/Select';
import Authenticated from '@/Layouts/Authenticated';
import { Head, Link, useForm, usePage } from '@inertiajs/react';
import { useState } from 'react';

export default function Edit(props) {
    const { vital, patients } = usePage().props;
    const [filter, setFilter] = useState('');
    const { data, setData, post, put, processing, errors } = useForm({
        patient_id: vital?.patient_id || '',
        blood_pressure: vital?.blood_pressure || '',
        heart_rate: vital?.heart_rate || '',
        temperature: vital?.temperature || '',
        respiratory_rate: vital?.respiratory_rate || '',
        oxygen_saturation: vital?.oxygen_saturation || '',
        weight: vital?.weight || '',
        height: vital?.height || '',
        bmi: vital?.bmi || '',
        notes: vital?.notes || '',
    });

    const submit = (e) => {
        e.preventDefault();
        if (vital) {
            put(route('vitals.update', vital.id));
        } else {
            post(route('vitals.store'));
        }
    };

    const onHandleChange = (event) => {
        setData(event.target.name, event.target.type === 'checkbox' ? event.target.checked : event.target.value);
    };

    const filteredOptions = patients?.filter((option) => option.label.toLowerCase().includes(filter.toLowerCase()));
    return (
        <Authenticated auth={props.auth} errors={props.errors} menu={props.menu} dashboardlogoUrl={props.dashboardlogoUrl}>
            <div className="container mx-auto my-12">
                <Head title="Edit Vital" />
                <div className="relative overflow-x-auto">
                    <form onSubmit={submit} className="mx-auto w-full space-y-4 pb-4 md:w-2/3">
                        <fieldset className="mt-4 border border-solid border-gray-300 p-3">
                            <legend>
                                <h1 className="text-2xl font-bold">{vital ? 'Edit Vital' : 'Add Vital'}</h1>
                            </legend>
                            <div>
                                <label className="block">Patient ID</label>
                                <Select
                                    value={data.patient_id}
                                    placeholder="Select Patient"
                                    name="patient_id"
                                    options={patients}
                                    disabled={vital ? true : false}
                                    className="w-full border p-2"
                                    onChange={onHandleChange}
                                    onKeyDown={(e) => {
                                        // Build up filter text as user types
                                        if (e.key.length === 1 && !e.ctrlKey && !e.metaKey) {
                                            setFilter((prev) => prev + e.key);
                                        }
                                        if (e.key === 'Backspace') {
                                            setFilter((prev) => prev.slice(0, -1));
                                        }
                                        if (e.key === 'Escape') {
                                            setFilter('');
                                        }
                                    }}
                                    size={filteredOptions?.length || 1} // keep it compact if you want dropdown style
                                    style={{ width: '100%', height: 'auto' }}
                                />
                                {errors.patient_id && <InputError className="mt-2 text-red-700" message={errors.patient_id} />}
                            </div>
                            <div className="mt-4">
                                <label className="block">Blood Pressure</label>
                                <Input
                                    name="blood_pressure"
                                    value={data.blood_pressure}
                                    handleChange={onHandleChange}
                                    className="w-full border p-2"
                                />
                            </div>
                            <div className="mt-4">
                                <label className="block">Heart Rate</label>
                                <Input
                                    name="heart_rate"
                                    type="number"
                                    value={data.heart_rate}
                                    handleChange={onHandleChange}
                                    className="w-full border p-2"
                                />
                            </div>
                            <div className="mt-4">
                                <label className="block">Temperature</label>
                                <Input
                                    name="temperature"
                                    type="number"
                                    step="0.1"
                                    value={data.temperature}
                                    handleChange={onHandleChange}
                                    className="w-full border p-2"
                                />
                            </div>
                            <div className="mt-4">
                                <label className="block">Notes</label>
                                <textarea
                                    name="notes"
                                    value={data.notes}
                                    onChange={onHandleChange}
                                    className="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                />
                            </div>
                            <div className="mt-4 flex gap-2">
                                <button disabled={processing} className="rounded-lg bg-lime-700 px-4 py-2 text-white">
                                    {vital ? 'Update' : 'Create'}
                                </button>
                                <Link href={route('vitals.index')} className="rounded-lg bg-gray-500 px-4 py-2 text-white">
                                    Cancel
                                </Link>
                            </div>
                        </fieldset>
                    </form>
                </div>
            </div>
        </Authenticated>
    );
}
