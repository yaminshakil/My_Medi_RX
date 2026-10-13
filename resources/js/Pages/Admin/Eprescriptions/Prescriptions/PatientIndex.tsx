import { ConfirmDialog } from '@/components/ConfirmDialog';
import PageHeader from '@/components/PageHeader';
import { Pagination } from '@/components/pagination';
import Table from '@/components/Table';
import AppLayout from '@/layouts/app-layout';
import { useCan } from '@/lib/can';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm, usePage } from '@inertiajs/react';
import { Printer } from 'lucide-react';
import { FormEventHandler, useState } from 'react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Prescriptions',
        href: '/prescriptions',
    },
];

export default function PatientIndex(props) {
    const { filters } = usePage().props;
    const [prescription, setPrescription] = useState(false);
    const [showConfirm, setShowConfirm] = useState(false);
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

    const handleDelete: FormEventHandler = (item) => {
        setPrescription(item);
        setShowConfirm(true);
    };

    const columns = [
        { key: 'prescription_number', label: 'Prescription No', render: (item) => item.prescription_number },
        {
            key: 'doctor',
            label: 'Doctor',
            render: (item) => (
                <div>
                    {item.doctor?.name || '—'}
                    <div>
                        <strong>Mobile: </strong>
                        {item.doctor.doctor.phone}
                    </div>
                    <div>
                        <strong>Designation: </strong>
                        {item.doctor.doctor.designation}
                    </div>
                    <div>
                        <strong>Specialization: </strong>
                        {item.doctor.doctor.specialization}
                    </div>
                </div>
            ),
        },
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

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Prescriptions" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <PageHeader
                    data={data}
                    perPageItem={filters.per_page}
                    handlePerPageChange={handlePerPageChange}
                    handleSearch={handleSearch}
                    placeholder="Search By Patient ..."
                    handleReset={handleReset}
                    handleCreate={null}
                    canCreate={null}
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
