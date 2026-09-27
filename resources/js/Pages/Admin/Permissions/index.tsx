import { ConfirmDialog } from '@/Components/ConfirmDialog';
import { AdminPageHeader } from '@/Components/AdminPageHeader';
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
        title: 'Permissions',
        href: '/permissions',
    },
];

export default function Index({ permissions }) {
    const { flash, filters } = usePage().props;
    const [showConfirm, setShowConfirm] = useState(false);
    const [permission, setPermission] = useState(false);
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
        router.get(route('permissions.index'), queryString, {
            preserveState: true,
            preserveScroll: true,
        });
    };

    const handleReset = () => {
        setData('search', '');
        router.get(
            route('permissions.index'),
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
        router.delete(route('permissions.destroy', permission.id), {
            preserveScroll: true,
        });
        setShowConfirm(false);
    };

    const handleCancel = () => {
        setShowConfirm(false);
    };

    const handlePerPageChange = (e) => {
        router.get(route('permissions.index'), { ...filters, per_page: e.target.value }, { preserveState: true });
    };

    const handleEdit: FormEventHandler = (item) => {
        router.get(route('permissions.edit', item.id));
    };

    const handleDelete: FormEventHandler = (item) => {
        setPermission(item);
        setShowConfirm(true);
    };

    const columns = [
        { key: '#', label: '#', render: (item) => item.id },
        { key: 'name', label: 'Name' },
        { key: 'created_at', label: 'Created At' },
    ];

    const handleCreate = () => {
        router.visit(route('permissions.create'));
    };
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Permissions" />
            <ToastContainer />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl px-4">
                <AdminPageHeader
                    breadcrumbs={breadcrumbs}
                    description="Manage system permissions for roles"
                    toolbar={
                        <PageHeader
                            data={data}
                            perPageItem={filters.per_page}
                            handlePerPageChange={handlePerPageChange}
                            handleSearch={handleSearch}
                            placeholder="Search Parmission ..."
                            handleReset={handleReset}
                            handleCreate={handleCreate}
                            canCreate={canCreate}
                        />
                    }
                />
                <div className="relative overflow-x-auto shadow-md sm:rounded-lg">
                    <div className="inline-block min-w-full">
                        <Table
                            items={permissions?.data}
                            columns={columns}
                            canEdit={canEdit}
                            canDelete={canDelete}
                            onEdit={handleEdit}
                            onDelete={handleDelete}
                            actionHeadClass="border p-4 w-24 text-center"
                            emptyMessage="No Parmission found."
                        />
                        <div className="mt-4">
                            <Pagination items={permissions} />
                        </div>
                    </div>
                </div>

                <ConfirmDialog
                    title="Delete Parmission"
                    message="Are you sure you want to delete this Parmission?"
                    onConfirm={handleConfirm}
                    onCancel={handleCancel}
                    isOpen={showConfirm}
                />
            </div>
        </AppLayout>
    );
}
