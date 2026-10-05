import { ConfirmDialog } from '@/Components/ConfirmDialog';
import PageHeader from '@/Components/PageHeader';
import { Pagination } from '@/Components/pagination';
import { Button } from '@/Components/ui/button';
import AppLayout from '@/Layouts/app-layout';
import { useCan } from '@/lib/can';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm, usePage } from '@inertiajs/react';
import dayjs from 'dayjs';
import { Edit, Trash2 } from 'lucide-react';
import React, { useEffect, useState } from 'react';
import { ToastContainer, toast } from 'react-toastify';

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Patients', href: '/patients' }];

export default function Index(props) {
    const { flash, filters } = usePage().props;
    const [expanded, setExpanded] = useState({});
    const [showConfirm, setShowConfirm] = useState(false);
    const [patientId, setPatientId] = useState(false);
    const canCreate = useCan('Create');
    const canEdit = useCan('Edit');
    const canDelete = useCan('Delete');

    const toggleExpand = (id) => {
        setExpanded({});
        setExpanded((prev) => ({ ...prev, [id]: !prev[id] }));
    };

    const { data, setData } = useForm({
        search: filters?.search || '',
    });

    const handleReset = () => {
        setData('search', '');
        router.get(
            route('patients.index'),
            {},
            {
                preserveState: true,
                preserveScroll: true,
            },
        );
    };

    const handleConfirm = () => {
        router.delete(route('patients.destroy', patientId), {
            preserveScroll: true,
            onSuccess: () => {
                setShowConfirm(false);
            },
        });
    };

    const handleCancel = () => {
        setShowConfirm(false);
    };

    // if Laravel flashed an ID, open PDF
    useEffect(() => {
        if (flash.prescription_id) {
            window.open(route('eprescription.pdf', flash.prescription_id), '_blank');
            return () => (flash.prescription_id = null);
        }
    }, [flash.prescription_id]);

    const handleSearch = (e) => {
        setData('search', e.target.value);
        router.get(route('patients.index'), { ...filters, search: e.target.value }, { preserveState: true });
    };

    const handlePerPageChange = (e) => {
        router.get(route('patients.index'), { ...filters, per_page: e.target.value }, { preserveState: true });
    };

    const handleSort = (field) => {
        const isSameField = filters.sort_by === field;
        const direction = isSameField && filters.sort_direction === 'asc' ? 'desc' : 'asc';
        router.get(
            route('patients.index'),
            {
                ...filters,
                sort_by: field,
                sort_direction: direction,
            },
            { preserveState: true },
        );
    };

    const renderSortIcon = (field) => {
        if (filters.sort_by !== field) return '⇅';
        return filters.sort_direction === 'asc' ? '↑' : '↓';
    };

    useEffect(() => {
        if (flash.message.success) {
            toast.success(flash.message.success);
        }
        if (flash.message.error) {
            toast.error(flash.message.error);
        }
    }, [flash]);

    useEffect(() => {
        if (props.patients.data.length > 0) {
            toggleExpand(props.patients.data[0].id);
        }
    }, []);

    const handleCreate = () => {
        router.visit(route('patients.create'));
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="All Patients" />
            <ToastContainer />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                {/* Header */}
                <PageHeader
                    data={data}
                    perPageItem={filters.per_page}
                    handlePerPageChange={handlePerPageChange}
                    handleSearch={handleSearch}
                    placeholder="Search Patient ..."
                    handleReset={handleReset}
                    handleCreate={handleCreate}
                    canCreate={canCreate}
                />
                <div className="relative overflow-x-auto shadow-md sm:rounded-lg">
                    <div className="inline-block min-w-full">
                        <table className="w-full border text-left text-sm text-gray-500 rtl:text-right dark:text-gray-400">
                            <thead className="bg-[var(--base-color)] text-xs text-gray-700 uppercase dark:bg-gray-700 dark:text-gray-400">
                                <tr className="bg-[var(--base-color)]">
                                    {['patient_number', 'name', 'phone', 'gender', 'address', 'city', 'created_at'].map((field) => (
                                        <th key={field} onClick={() => handleSort(field)} className="cursor-pointer border p-4 text-white">
                                            <div className="flex items-center space-x-1">
                                                <span className="capitalize">{field.replace('_', ' ')}</span>
                                                <span>{renderSortIcon(field)}</span>
                                            </div>
                                        </th>
                                    ))}

                                    <th className="border p-4 text-center text-white">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                {props.patients?.data?.length > 0 ? (
                                    props.patients.data.map((patient) => (
                                        <React.Fragment key={patient.id}>
                                            <tr
                                                onLoad={() => {
                                                    toggleExpand(patient.id);
                                                    alert(patient.id);
                                                }}
                                                className="border border-gray-200 bg-white hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:hover:bg-gray-600"
                                            >
                                                <td className="border p-4">{patient.patient_number}</td>
                                                <td className="border p-4">
                                                    <button onClick={() => toggleExpand(patient.id)} className="text-blue-500 underline">
                                                        {patient.name}
                                                    </button>
                                                </td>
                                                <td className="border p-4">{patient.phone}</td>
                                                <td className="border p-4">{patient.gender}</td>
                                                <td className="border p-4">{patient.address}</td>
                                                <td className="border p-4">{patient.city}</td>
                                                <td className="border p-4">{patient.created_at}</td>
                                                <td className="border p-4 text-center align-middle">
                                                    <div className="flex items-center justify-center gap-2">
                                                        {canEdit && (
                                                            <Button
                                                                onClick={() => router.get(route('patients.edit', patient.id))}
                                                                className="inline-flex cursor-pointer items-center text-white"
                                                            >
                                                                <Edit className="mr-1 h-4 w-4" /> Edit
                                                            </Button>
                                                        )}
                                                        {canDelete && (
                                                            <Button
                                                                variant="destructive"
                                                                className="cursor-pointer text-white md:ml-2"
                                                                onClick={() => {
                                                                    setShowConfirm(true);
                                                                    setPatientId(patient.id);
                                                                }}
                                                            >
                                                                <Trash2 className="mr-1 h-4 w-4" /> Delete
                                                            </Button>
                                                        )}
                                                    </div>
                                                </td>
                                            </tr>
                                            {expanded[patient.id] && (
                                                <tr>
                                                    <td colSpan="6" className="border bg-gray-50 p-4">
                                                        <div className="grid grid-cols-2 gap-4">
                                                            <div>
                                                                <h2 className="mb-2 font-bold">Prescriptions</h2>
                                                                {patient?.prescriptions?.length > 0 ? (
                                                                    <ul className="ml-5 list-disc">
                                                                        {patient.prescriptions.map((p) => (
                                                                            <li className="mt-5" key={p.id}>
                                                                                <a
                                                                                    href={route('eprescription.pdf', p.uuid)}
                                                                                    className="rounded-lg bg-[var(--btn-base-color)] px-2 py-2 text-white hover:bg-[var(--btn-base-hover-color)]"
                                                                                    target="_blank"
                                                                                >
                                                                                    {dayjs(p.created_at, ['YYYY-MM-DD', 'HH:mm:ss']).format(
                                                                                        'YYYY-MM-DD HH:mm:ss',
                                                                                    )}{' '}
                                                                                    - {p.diagnosis || 'No diagnosis'}
                                                                                </a>
                                                                            </li>
                                                                        ))}
                                                                    </ul>
                                                                ) : (
                                                                    <p className="text-gray-500">No prescriptions</p>
                                                                )}
                                                            </div>

                                                            <div>
                                                                <h2 className="mb-2 font-bold">Vitals</h2>
                                                                {patient?.vitals.length > 0 ? (
                                                                    <ul className="ml-5 list-disc">
                                                                        {patient.vitals.map((v) => (
                                                                            <div className="mb-1 border-b" key={v.id}>
                                                                                {v.blood_pressure && <li>BP: {v.blood_pressure || ' '}</li>}
                                                                                {v.heart_rate && <li>HR: {v.heart_rate || ' '}</li>}
                                                                                {v.temperature && <li>Temp: {v.temperature || ' '}</li>}
                                                                                {v.oxygen_saturation && <li>SpO₂: {v.oxygen_saturation || ' '}</li>}
                                                                                {v.respiratory_rate && <li>RR: {v.respiratory_rate || ' '}</li>}
                                                                                {v.weight && <li>Wt: {v.weight || ' '}</li>}
                                                                                {v.height && <li>Ht: {v.height || ' '}</li>}
                                                                                {v.bmi && <li>BMI: {v.bmi || ' '}</li>}
                                                                                {v.notes && <li>Notes: {v.notes || ' '}</li>}
                                                                            </div>
                                                                        ))}
                                                                    </ul>
                                                                ) : (
                                                                    <p className="text-gray-500">No vitals recorded</p>
                                                                )}
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td colSpan="2" className="border bg-gray-50 p-4">

                                                        <Button
                                                            className="cursor-pointer rounded-lg !p-3 text-white md:ml-2"
                                                            onClick={() => {
                                                                router.visit(route('patients.show', patient.id));
                                                            }}
                                                        >
                                                            View Details
                                                        </Button>

                                                    </td>
                                                </tr>
                                            )}
                                        </React.Fragment>
                                    ))
                                ) : (
                                    <tr>
                                        <td className="p-4 text-center" colSpan="8">
                                            No Patients found.
                                        </td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                        <div className="mt-4">
                            <Pagination items={props.patients} />
                        </div>
                    </div>
                </div>
            </div>
            <ConfirmDialog
                title="Delete Patient"
                message="Are you sure you want to delete this Patient?"
                onConfirm={handleConfirm}
                onCancel={handleCancel}
                isOpen={showConfirm}
            />
        </AppLayout>
    );
}
