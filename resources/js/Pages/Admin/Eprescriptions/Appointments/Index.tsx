import AddVital from '@/components/AddVital';
import { CommonModal } from '@/components/CommonModal';
import { ConfirmDialog } from '@/components/ConfirmDialog';
import PageHeader from '@/components/PageHeader';
import { Pagination } from '@/components/pagination';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';
import { useCan } from '@/lib/can';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router, useForm, usePage } from '@inertiajs/react';
import { Select } from 'antd';
import dayjs from 'dayjs';
import { Edit, Trash2 } from 'lucide-react';
import React, { useEffect, useState } from 'react';
import { ToastContainer, toast } from 'react-toastify';

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Appointments', href: '/appointments' }];

const appoinmentstatus = [
    { value: 'pending', label: 'Pending' },
    { value: 'confirmed', label: 'Confirmed' },
    { value: 'cancelled', label: 'Cancelled' },
    { value: 'completed', label: 'Completed' },
    { value: 'absent', label: 'Absent' },
];

export default function Index(props) {
    const { flash, filters } = usePage().props;
    const [expanded, setExpanded] = useState({});
    const [showConfirm, setShowConfirm] = useState(false);
    const [appointmentId, setAppointmentId] = useState(false);
    const [showAddVitalModal, setShowAddVitalModal] = useState(false);
    const canCreate = useCan('Appointment Create');
    const canEdit = useCan('Appointment Edit');
    const canDelete = useCan('Appointment Delete');
    const canCreateVital = useCan('Create');
    const canFollowup = useCan('Edit');

    const toggleExpand = (id) => {
        setExpanded({});
        setExpanded((prev) => ({ ...prev, [id]: !prev[id] }));
    };

    const { data, setData, reset } = useForm({
        search: props.filters?.search || '',
    });

    const {
        data: vitalData,
        setData: setVitalData,
        post: postVital,
    } = useForm({
        patient_id: '',
        blood_pressure: '',
        heart_rate: '',
        temperature: '',
        respiratory_rate: '',
        oxygen_saturation: '',
        weight: '',
        height: '',
        bmi: '',
        notes: '',
    });

    const handleSearch = (e) => {
        // console.log(e.target.value);
        setData('search', e.target.value);
        router.get(route('appointments.index'), { ...filters, search: data.search }, { preserveState: true });
    };

    const handlePerPageChange = (e) => {
        router.get(route('appointments.index'), { ...filters, per_page: e.target.value }, { preserveState: true });
    };

    const handleReset = () => {
        setData('search', '');
        router.get(
            route('appointments.index'),
            {},
            {
                preserveState: true,
                preserveScroll: true,
                onSuccess: () => {
                    reset();
                },
            },
        );
    };

    const handleConfirm = () => {
        router.delete(route('appointments.destroy', appointmentId), {
            preserveScroll: true,
            onSuccess: () => {
                setShowConfirm(false);
            },
        });
    };

    const handleCancel = () => {
        setShowConfirm(false);
    };

    const handleAddvital = () => {
        postVital(route('vitals.store'), {
            preserveScroll: true,
            onSuccess: () => {
                setShowAddVitalModal(false);
            },
        });
    };

    const handleAddvitalCancel = () => {
        setShowAddVitalModal(false);
    };

    const onHandleChange = (event) => {
        setVitalData(event.target.name, event.target.type === 'checkbox' ? event.target.checked : event.target.value);
    };

    useEffect(() => {
        if (props.appointments.data.length > 0) {
            toggleExpand(props.appointments.data[0].id);
        }
    }, []);

    // if Laravel flashed an ID, open PDF
    useEffect(() => {
        if (flash.prescription_id) {
            window.open(route('eprescription.pdf', flash.prescription_id), '_blank');
            return () => (flash.prescription_id = null);
        }
    }, [flash.prescription_id]);

    const handleSort = (field) => {
        const isSameField = filters.sort_by === field;
        const direction = isSameField && filters.sort_direction === 'asc' ? 'desc' : 'asc';
        router.get(
            route('appointments.index'),
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

    const handleChangeStatus = (id, value) => {
        router.post(route('appointments.changestatus'), { status: value, appointment_id: id }, { preserveState: true });
    };

    const handleEdit = (id) => {
        router.get(route('appointments.edit', id));
    };

    useEffect(() => {
        if (flash?.message?.success) {
            toast.success(flash.message.success);
        }
        if (flash?.message?.error) {
            toast.error(flash.message.error);
        }
    }, [flash]);

    const handleCreate = () => {
        router.visit(route('appointments.create_for_patient', { doctor_id: props?.doctor_id, patient_id: props?.patient_id }));
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="All Appointments" />
            <ToastContainer />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <PageHeader
                    data={data}
                    perPageItem={filters.per_page}
                    handlePerPageChange={handlePerPageChange}
                    handleSearch={handleSearch}
                    placeholder="Search Appointments by Patient ..."
                    handleReset={handleReset}
                    handleCreate={handleCreate}
                    canCreate={canCreate && props?.doctor_id}
                    CreateBtn="Book Appointment"
                />

                <div className="relative overflow-x-auto shadow-md sm:rounded-lg">
                    <div className="inline-block min-w-full">
                        <table className="w-full border text-left text-sm text-gray-500 rtl:text-right dark:text-gray-400">
                            <thead className="bg-[var(--base-color)] text-xs text-gray-700 uppercase dark:bg-gray-700 dark:text-gray-400">
                                <tr className="bg-[var(--base-color)]">
                                    {['appointment_number', 'name', 'appointment_date', 'appointment_time', 'status', 'city', 'created_at'].map(
                                        (field) => (
                                            <th key={field} onClick={() => handleSort(field)} className="cursor-pointer border px-4 py-4 text-white">
                                                <div className="flex items-center space-x-1">
                                                    <span className="capitalize">{field.replace('_', ' ')}</span>
                                                    <span>{renderSortIcon(field)}</span>
                                                </div>
                                            </th>
                                        ),
                                    )}
                                    <th scope="col" className="border px-6 py-4 text-center text-white">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                {props.appointments?.data?.length > 0 ? (
                                    props.appointments.data.map((appointment) => (
                                        <React.Fragment key={appointment.id}>
                                            <tr className="border border-gray-200 bg-white hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:hover:bg-gray-600">
                                                <td className="border px-4 py-4">{appointment.appointment_number}</td>
                                                <td className="border px-4 py-4">
                                                    <button
                                                        onClick={() => toggleExpand(appointment.id)}
                                                        className="cursor-pointer text-[var(--base-color)] underline"
                                                    >
                                                        {appointment.patient?.name}
                                                    </button>
                                                    <div>
                                                        <strong>Mobile: </strong>
                                                        {appointment.patient?.phone}
                                                    </div>
                                                    <div>
                                                        <strong>Gender: </strong>
                                                        {appointment.patient?.gender}
                                                    </div>
                                                </td>
                                                <td className="border px-4 py-4">{appointment.appointment_date}</td>
                                                <td className="border px-4 py-4">{appointment.appointment_time}</td>
                                                <td className="border px-6 py-2">
                                                    <Select
                                                        placeholder="Select Status"
                                                        optionFilterProp="label"
                                                        style={{ width: '110px' }}
                                                        name="status"
                                                        value={appointment.status || undefined}
                                                        onChange={(value) => handleChangeStatus(appointment.id, value)}
                                                        className="basic-single m-0 h-10 w-full"
                                                        options={appoinmentstatus}
                                                    />
                                                </td>
                                                <td className="border px-4 py-4">{appointment.patient?.city}</td>
                                                <td className="border px-4 py-4">{appointment.created_at}</td>
                                                <td className="border px-4 py-4 align-middle">
                                                    <div className="flex h-full items-center justify-center gap-2">
                                                        <span>{canEdit}</span>
                                                        {canEdit && (
                                                            <Button
                                                                data-testid="edit-btn"
                                                                onClick={() => handleEdit(appointment.id)}
                                                                className="inline-flex cursor-pointer items-center text-white"
                                                            >
                                                                <Edit className="mr-1 h-4 w-4" /> Edit
                                                            </Button>
                                                        )}
                                                        {canDelete && (
                                                            <Button
                                                                variant="destructive"
                                                                className="cursor-pointer text-white"
                                                                onClick={() => {
                                                                    setShowConfirm(true);
                                                                    setAppointmentId(appointment.id);
                                                                }}
                                                            >
                                                                <Trash2 className="mr-1 h-4 w-4" />
                                                                Delete
                                                            </Button>
                                                        )}
                                                    </div>
                                                </td>
                                            </tr>
                                            {expanded[appointment.id] && (
                                                <tr>
                                                    <td colSpan="6" className="border bg-gray-50 p-4">
                                                        <div className="grid grid-cols-2 gap-4">
                                                            <div>
                                                                <h2 className="mb-2 font-bold">Prescriptions</h2>
                                                                {appointment?.prescriptions?.length > 0 ? (
                                                                    <ul className="ml-5 list-disc">
                                                                        {appointment?.prescriptions.map((p, index) => (
                                                                            <li className="mt-3" key={p.id}>
                                                                                <a
                                                                                    href={route('eprescription.pdf', p.uuid)}
                                                                                    className="rounded-lg bg-[var(--base-color)] px-1 py-1 text-white"
                                                                                    target="_blank"
                                                                                >
                                                                                    {dayjs(p.created_at, ['YYYY-MM-DD', 'HH:mm:ss']).format(
                                                                                        'YYYY-MM-DD HH:mm:ss',
                                                                                    )}{' '}
                                                                                    - {p.diagnosis || 'No diagnosis'}
                                                                                </a>
                                                                                {index == 0 && appointment.status != 'completed' && canFollowup && (
                                                                                    <Link
                                                                                        href={route('prescriptions.followup', {
                                                                                            id: p.id,
                                                                                            appointment_id: appointment.id,
                                                                                        })}
                                                                                        className="ml-2 rounded-lg bg-[var(--base-color)] px-1 py-1 text-white"
                                                                                        target="_blank"
                                                                                    >
                                                                                        Followup
                                                                                    </Link>
                                                                                )}
                                                                            </li>
                                                                        ))}
                                                                    </ul>
                                                                ) : (
                                                                    <p className="text-gray-500">No prescriptions</p>
                                                                )}
                                                            </div>

                                                            <div>
                                                                <h2 className="mb-2 font-bold">Vitals</h2>
                                                                {appointment?.vitals?.length > 0 ? (
                                                                    <ul className="ml-5 list-disc">
                                                                        {appointment.vitals.map((v) => (
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
                                                    <td colSpan="2" className="border bg-gray-50 p-4 align-middle">
                                                        {appointment.status != 'completed' && (
                                                            <div className="flex h-full items-center justify-center gap-2">
                                                                {canCreateVital && (
                                                                    <Button
                                                                        className="cursor-pointer text-white"
                                                                        onClick={() => {
                                                                            setShowAddVitalModal(true);
                                                                            setVitalData('patient_id', appointment.patient.id);
                                                                        }}
                                                                    >
                                                                        + Add Vital
                                                                    </Button>
                                                                )}
                                                            </div>
                                                        )}
                                                    </td>
                                                </tr>
                                            )}
                                        </React.Fragment>
                                    ))
                                ) : (
                                    <tr>
                                        <td className="p-4 text-center" colSpan="8">
                                            No Appointments found.
                                        </td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                        <div className="mt-4">
                            <Pagination items={props.appointments} />
                        </div>
                    </div>
                </div>
            </div>
            <ConfirmDialog
                title="Delete Appointment"
                message="Are you sure you want to delete this Appointment?"
                onConfirm={handleConfirm}
                onCancel={handleCancel}
                isOpen={showConfirm}
            />
            <CommonModal
                onConfirm={handleAddvital}
                onCancel={handleAddvitalCancel}
                isOpen={showAddVitalModal}
                onClose={() => setShowAddVitalModal(false)}
                title="Add Vital"
            >
                <AddVital vitalData={vitalData} setVitalData={setVitalData} onHandleChange={onHandleChange} />
            </CommonModal>
        </AppLayout>
    );
}
