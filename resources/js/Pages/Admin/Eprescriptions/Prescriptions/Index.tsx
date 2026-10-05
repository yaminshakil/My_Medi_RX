import { ConfirmDialog } from '@/Components/ConfirmDialog';
import PageHeader from '@/Components/PageHeader';
import { Pagination } from '@/Components/pagination';
import Table from '@/Components/Table';
import AppLayout from '@/Layouts/app-layout';
import { useCan } from '@/lib/can';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm, usePage } from '@inertiajs/react';
import { Printer } from 'lucide-react';
import { FormEventHandler, useEffect, useState } from 'react';
import { ToastContainer, toast } from 'react-toastify';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Prescriptions',
        href: '/prescriptions',
    },
];

export default function Index(props) {
    const { flash, filters } = usePage().props;
    const [showConfirm, setShowConfirm] = useState(false);
    const [prescription, setPrescription] = useState(false);
    const canCreate = useCan('Create');
    const canDelete = useCan('Delete');

    const { data, setData } = useForm({
        search: filters?.search || '',
    });
    const handleSearch = (e) => {
        setData('search', e.target.value);
        router.get(route('prescriptions.index'), { ...filters, search: e.target.value }, { preserveState: true });
    };

    const handleReset = () => {
        setData('search', '');
        router.get(
            route('prescriptions.index'),
            {},
            {
                preserveState: true,
                preserveScroll: true,
            },
        );
    };

    const handlePerPageChange = (e) => {
        router.get(route('prescriptions.index'), { ...filters, perPage: e.target.value }, { preserveState: true });
    };

    const handleConfirm = () => {
        router.delete(route('prescriptions.destroy', prescription.id), {
            preserveScroll: true,
            onSuccess: () => {
                setShowConfirm(false);
            },
        });
    };

    const handleCancel = () => {
        setShowConfirm(false);
    };

    useEffect(() => {
        if (flash.message.success) {
            toast.success(flash.message.success);
        }
        if (flash.message.error) {
            toast.error(flash.message.error);
        }
    }, [flash]);

    const handleDelete: FormEventHandler = (item) => {
        setPrescription(item);
        setShowConfirm(true);
    };

    const columns = [
        { key: 'prescription_number', label: 'Prescription No', render: (item) => item.prescription_number },
        {
            key: 'patient',
            label: 'Patient',
            render: (item) => (
                <div>
                    {item.patient?.name}
                    <div>
                        <strong>Mobile: </strong>
                        {item.patient?.phone}
                    </div>
                    <div className="capitalize">
                        <strong>Gender: </strong>
                        {item.patient?.gender}
                    </div>
                </div>
            ),
        },
        { key: 'doctor', label: 'Doctor', render: (item) => item.doctor?.name || '—' },
        { key: 'date', label: 'Date', render: (item) => item.created_at || '—' },
        {
            key: 'status',
            label: 'Status',
            className: 'text-center',
            render: (item) => <span className={`badge ${item.status ? 'badge--success' : 'badge--warning'}`}>{item.status}</span>,
        },
    ];

    const PrintPrescription = ({ prescription }) => {
        return (
            <a
                target="_blank"
                href={route('eprescription.pdf', prescription.uuid)}
                className="rounded-lg bg-[var(--btn-base-color)] p-3 text-white hover:bg-[var(--btn-base-hover-color)] hover:underline"
            >
                <Printer size={20} />
            </a>
        );
    };

    const handleCreate = () => {
        router.visit(route('prescriptions.saveandnew'));
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Prescriptions" />
            <ToastContainer />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                {/* Header */}
                <PageHeader
                    data={data}
                    perPageItem={filters.per_page}
                    handlePerPageChange={handlePerPageChange}
                    handleSearch={handleSearch}
                    placeholder="Search By Patient ..."
                    handleReset={handleReset}
                    handleCreate={handleCreate}
                    canCreate={canCreate}
                    CreateBtn="New Prescription"
                />

                <div className="relative overflow-x-auto shadow-md sm:rounded-lg">
                    <div className="inline-block min-w-full">
                        <Table
                            items={props.prescriptions?.data}
                            columns={columns}
                            canDelete={canDelete}
                            onDelete={handleDelete}
                            actionHeadClass="border p-4 w-24 text-center"
                            renderActions={(prescription) => <PrintPrescription prescription={prescription} />}
                            emptyMessage="No Prescriptions found."
                        />
                        <div className="mt-4">
                            <Pagination items={props.prescriptions} />
                        </div>
                    </div>
                </div>
            </div>

            <ConfirmDialog
                title="Delete Prescription"
                message="Are you sure you want to delete this Prescription?"
                onConfirm={handleConfirm}
                onCancel={handleCancel}
                isOpen={showConfirm}
            />
        </AppLayout>
    );
}
