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
import MoreDropdown from './MoreDropdown';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Doctors',
        href: '/doctors',
    },
];

export default function Index({ doctors }) {
    const { flash, filters } = usePage().props;
    const [showConfirm, setShowConfirm] = useState(false);
    const [doctor, setDoctor] = useState(false);
    const canCreate = useCan('Create');
    const canEdit = useCan('Edit');
    const canDelete = useCan('Delete');

    const { data, setData } = useForm({
        search: filters?.search || '',
    });

    const handleSearch = (e: React.ChangeEvent<HTMLInputElement>) => {
        const value = e.target.value;
        setData('search', value);
        const queryString = value ? { search: value } : {};
        router.get(route('doctors.index'), queryString, {
            preserveState: true,
            preserveScroll: true,
        });
    };

    const handleReset = () => {
        setData('search', '');
        router.get(
            route('doctors.index'),
            {},
            {
                preserveState: true,
                preserveScroll: true,
            },
        );
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
        router.delete(route('doctors.destroy', doctor.doctor), {
            preserveScroll: true,
        });
        setShowConfirm(false);
    };

    const handleCancel = () => {
        setShowConfirm(false);
    };

    const handlePerPageChange = (e) => {
        router.get(route('doctors.index'), { ...filters, per_page: e.target.value }, { preserveState: true });
    };

    const handleEdit: FormEventHandler = (item) => {
        router.get(route('doctors.edit', { doctor: item.doctor.uuid }));
    };

    const handleDelete: FormEventHandler = (item) => {
        setDoctor(item);
        setShowConfirm(true);
    };

    const columns = [
        { key: '#', label: '#', render: (item) => item.id },
        { key: 'name', label: 'Name' },
        { key: 'email', label: 'Email' },
        {
            key: 'featured',
            label: 'Featured',
            className: 'text-center',
            render: (item) => (
                <span className={`badge ${item.featured ? 'badge--success' : 'badge--warning'}`}>{item.featured ? 'Featured' : 'Non Featured'}</span>
            ),
        },
        {
            key: 'active',
            label: 'Status',
            className: 'text-center',
            render: (item) => (
                <span className={`badge ${item.active ? 'badge--success' : 'badge--warning'}`}>{item.active ? 'Active' : 'Inactive'}</span>
            ),
        },
        { key: 'created_at', label: 'Created At' },
    ];

    const handleCreate = () => {
        router.visit(route('doctors.create'));
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Doctors" />
            <ToastContainer />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <PageHeader
                    data={data}
                    perPageItem={filters.per_page}
                    handlePerPageChange={handlePerPageChange}
                    handleSearch={handleSearch}
                    placeholder="Search doctor ..."
                    handleReset={handleReset}
                    handleCreate={handleCreate}
                    canCreate={canCreate}
                />

                <div className="relative overflow-x-auto shadow-md sm:rounded-lg">
                    <div className="inline-block min-w-full">
                        <Table
                            items={doctors.data}
                            columns={columns}
                            canEdit={canEdit}
                            canDelete={canDelete}
                            onEdit={handleEdit}
                            onDelete={handleDelete}
                            renderActions={(doctor) => <MoreDropdown doctor={doctor?.doctor} />}
                            emptyMessage="No Doctor found."
                        />
                        <div className="mt-4">
                            <Pagination items={doctors} />
                        </div>
                    </div>
                </div>

                <ConfirmDialog
                    title="Delete Doctor"
                    message={`Are you sure you want to delete this <b>${doctor?.name}</b>?`}
                    onConfirm={handleConfirm}
                    onCancel={handleCancel}
                    isOpen={showConfirm}
                />
            </div>
        </AppLayout>
    );
}
