import { ConfirmDialog } from '@/Components/ConfirmDialog';
import { Pagination } from '@/Components/pagination';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import Authenticated from '@/Layouts/Authenticated';
import { Head, Link, router, useForm, usePage } from '@inertiajs/react';
import { Pencil, Trash2, X } from 'lucide-react';
import { useState } from 'react';

export default function Index(props) {
    const { vitals } = usePage().props;
    console.log(vitals);
    const [showAlert, setShowAlert] = useState(true);
    const [showConfirm, setShowConfirm] = useState(false);
    const [vitalId, setVitalId] = useState(false);

    const { data, setData } = useForm({
        search: props.filters?.search || '',
    });

    const onHandleChange = (e) => {
        const value = e.target.value;
        setData('search', value);
        const queryString = value ? { search: value } : {};
        router.get(route('vitals.index'), queryString, {
            preserveState: true,
            preserveScroll: true,
        });
    };

    const handleReset = () => {
        setData('search', '');
        router.get(
            route('vitals.index'),
            {},
            {
                preserveState: true,
                preserveScroll: true,
            },
        );
    };

    const handleConfirm = () => {
        router.delete(route('vitals.destroy', vitalId), {
            preserveScroll: true,
            onSuccess: () => {
                setShowConfirm(false);
            },
        });
    };

    const handleCancel = () => {
        setShowConfirm(false);
    };

    return (
        <Authenticated auth={props.auth} errors={props.errors} menu={props.menu} dashboardlogoUrl={props.dashboardlogoUrl}>
            <Head title="All Patients" />
            <div className="container mx-auto mt-4">
                <section className="mx-4 py-12">
                    <h1 className="text-xl font-bold">Vitals</h1>
                    {props?.flash?.success && showAlert && (
                        <div className="mt-4 flex w-full items-center justify-center">
                            <div
                                className="relative mb-3 mb-4 w-full rounded-lg bg-green-100 px-6 py-5 text-center text-base text-green-700"
                                role="alert"
                            >
                                {props?.flash?.success}
                                <button
                                    className="absolute top-0 right-0 mt-4 mr-6 bg-transparent text-2xl leading-none font-semibold outline-none focus:outline-none"
                                    onClick={() => setShowAlert(false)}
                                >
                                    <span>×</span>
                                </button>
                            </div>
                        </div>
                    )}
                    <div className="flex items-center">
                        <TextInput
                            type="text"
                            value={data.search}
                            onChange={onHandleChange}
                            className="h-10 w-full md:w-1/3"
                            placeholder="Search Vital ..."
                            name="search"
                        />
                        <button type="button" onClick={handleReset} className="ml-2 h-10 cursor-pointer rounded-lg bg-red-600 px-4 py-3">
                            <X className="mb-3 text-white" size={20} />
                        </button>
                    </div>
                    <div className="overflow-scroll p-6 md:overflow-hidden">
                        <table className="w-full border text-left text-sm text-gray-500 rtl:text-right dark:text-gray-400">
                            <thead className="bg-gray-50 text-xs text-gray-700 uppercase dark:bg-gray-700 dark:text-gray-400">
                                <tr className="bg-gray-100">
                                    <th className="border px-6 py-4">Patient</th>
                                    <th className="border px-6 py-4">Blood Pressure</th>
                                    <th className="border px-6 py-4">Heart Rate</th>
                                    <th className="border px-6 py-4">Temperature</th>
                                    <th className="border px-6 py-4">Created At</th>
                                    <th className="border px-6 py-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                {vitals.data.map((v) => (
                                    <tr key={v.id}>
                                        <td className="border px-6 py-2">{v?.name}</td>
                                        <td className="border px-6 py-2">{v.blood_pressure}</td>
                                        <td className="border px-6 py-2">{v.heart_rate}</td>
                                        <td className="border px-6 py-2">{v.temperature}</td>
                                        <td className="border px-6 py-2">{v.created_at}</td>
                                        <td className="border px-6 py-2">
                                            <Link
                                                as="button"
                                                className="mb-2 cursor-pointer rounded-lg bg-lime-600 p-3 text-white hover:bg-lime-700"
                                                href={route('vitals.edit', v.id)}
                                            >
                                                <Pencil size={20} />
                                            </Link>
                                            <PrimaryButton
                                                className="cursor-pointer rounded-lg bg-red-600 !p-3 text-white hover:bg-red-800 md:ml-2"
                                                onClick={() => {
                                                    setShowConfirm(true);
                                                    setVitalId(v.id);
                                                }}
                                            >
                                                <Trash2 size={20} />
                                            </PrimaryButton>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                        <div className="mt-4">
                            <Pagination items={vitals} />
                        </div>
                    </div>
                </section>
                <ConfirmDialog
                    message="Are you sure you want to delete this Patient?"
                    onConfirm={handleConfirm}
                    onCancel={handleCancel}
                    isOpen={showConfirm}
                    onClose={() => setShowConfirm(false)}
                />
            </div>
        </Authenticated>
    );
}
