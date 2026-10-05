import { ConfirmDialog } from '@/Components/ConfirmDialog';
import PageHeader from '@/Components/PageHeader';
import { Pagination } from '@/Components/pagination';
import Table from '@/Components/Table';
import AppLayout from '@/Layouts/app-layout';
import { useCan } from '@/lib/can';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm, usePage } from '@inertiajs/react';
import { FormEventHandler, useEffect, useState } from 'react';
import { ToastContainer, toast } from 'react-toastify';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Medicines',
        href: '/medicines',
    },
];

export default function Index() {
    const { medicines, flash, filters } = usePage().props;
    const [showConfirm, setShowConfirm] = useState(false);
    const [medicine, setMedicine] = useState([]);
    const canCreate = useCan('Create');
    const canEdit = useCan('Edit');
    const canDelete = useCan('Delete');

    const { data, setData } = useForm({
        search: filters?.search || '',
    });

    const handleReset = () => {
        setData('search', '');
        router.get(
            route('medicines.index'),
            {},
            {
                preserveState: true,
                preserveScroll: true,
            },
        );
    };

    const handleSearch = (e) => {
        setData('search', e.target.value);
        router.get(route('medicines.index'), { ...filters, search: e.target.value }, { preserveState: true });
    };
    const handlePerPageChange = (e) => {
        router.get(route('medicines.index'), { ...filters, per_page: e.target.value }, { preserveState: true });
    };

    useEffect(() => {
        if (flash.message.success) {
            toast.success(flash.message.success);
        }
        if (flash.message.error) {
            toast.error(flash.message.error);
        }
    }, [flash]);

    const handleConfirm = () => {
        router.delete(route('medicines.destroy', medicine.id), {
            preserveScroll: true,
            onSuccess: () => {
                setShowConfirm(false);
            },
        });
    };

    const handleCancel = () => {
        setShowConfirm(false);
    };

    const handleEdit: FormEventHandler = (item) => {
        router.get(route('medicines.edit', item.id));
    };

    const handleDelete: FormEventHandler = (item) => {
        setMedicine(item);
        setShowConfirm(true);
    };

    const columns = [
        { key: '#', label: '#', render: (item) => item.id },
        { key: 'brand_name', label: 'Brand Name' },
        { key: 'generic_name', label: 'Generic Name' },
        { key: 'strength', label: 'Strength' },
        { key: 'type', label: 'Type' },
    ];

    const handleCreate = () => {
        router.visit(route('medicines.create'));
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Medicines" />
            <ToastContainer />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                {/* Header */}
                <PageHeader
                    data={data}
                    perPageItem={filters.per_page}
                    handlePerPageChange={handlePerPageChange}
                    handleSearch={handleSearch}
                    placeholder="Search Medicine ..."
                    handleReset={handleReset}
                    handleCreate={handleCreate}
                    canCreate={canCreate}
                />

                <div className="relative overflow-x-auto shadow-md sm:rounded-lg">
                    <div className="inline-block min-w-full">
                        <Table
                            items={medicines?.data}
                            columns={columns}
                            canEdit={canEdit}
                            canDelete={canDelete}
                            onEdit={handleEdit}
                            onDelete={handleDelete}
                            actionHeadClass="border p-4 w-24 text-center"
                            emptyMessage="No Medicine found."
                        />
                        <div className="mt-4">
                            <Pagination items={medicines} />
                        </div>
                    </div>
                </div>
                <ConfirmDialog
                    title="Delete Medicine"
                    message={`Are you sure you want to delete <b>${medicine.brand_name}</b>?`}
                    onConfirm={handleConfirm}
                    onCancel={handleCancel}
                    isOpen={showConfirm}
                />
            </div>
        </AppLayout>
    );
}
