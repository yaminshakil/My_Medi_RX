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
import { AppPageProps } from '@/types';
import { Hospital } from 'lucide-react';

export default function Index({ hospital }) {
    const { translations } = usePage<AppPageProps>().props;
    const { flash, filters } = usePage().props;
    const [showConfirm, setShowConfirm] = useState(false);
    const [selectedHospital, setSelectedHospital] = useState<any>(null);

    const canCreate = useCan('Create');
    const canEdit = useCan('Edit');
    const canDelete = useCan('Delete');

    const { data, setData } = useForm({
        search: filters?.search || '',
        status: filters?.status ?? '',
    });

    /**
     * Search hospitals
     */
    const handleSearch = (e: React.ChangeEvent<HTMLInputElement>) => {
        const value = e.target.value;

        setData('search', value);

        const queryString = {
            ...(value ? { search: value } : {}),
            ...(data.status !== '' ? { status: data.status } : {}),
        };

        router.get(
            route('hospital.index'),
            queryString,
            {
                preserveState: true,
                preserveScroll: true,
            }
        );
    };

    /**
     * Reset filters
     */
    const handleReset = () => {
        setData({
            search: '',
            status: '',
        });

        router.get(
            route('hospital.index'),
            {},
            {
                preserveState: true,
                preserveScroll: true,
            }
        );
    };

    /**
     * Status filter
     */
    const handleStatusChange = (
        e: React.ChangeEvent<HTMLSelectElement>
    ) => {
        const value = e.target.value;

        setData('status', value);

        const queryString = {
            ...(data.search ? { search: data.search } : {}),
            ...(value !== '' ? { status: value } : {}),
        };

        router.get(
            route('hospital.index'),
            queryString,
            {
                preserveState: true,
                preserveScroll: true,
            }
        );
    };

    /**
     * Per page
     */
    const handlePerPageChange = (e) => {
        router.get(
            route('hospital.index'),
            {
                ...filters,
                per_page: e.target.value,
            },
            {
                preserveState: true,
            }
        );
    };

    /**
     * Create
     */
    const handleCreate = () => {
        router.visit(route('hospital.create'));
    };

    /**
     * Edit
     */
    const handleEdit: FormEventHandler = (item) => {
        router.get(
            item.edit_url ??
            route('hospital.edit', { id: item.id })
        );
    };

    /**
     * Delete confirmation
     */
    const handleDelete: FormEventHandler = (item) => {
        setSelectedHospital(item);
        setShowConfirm(true);
    };

    /**
     * Confirm delete
     */
    const handleConfirm = () => {
        if (!selectedHospital) {
            return;
        }

        router.delete(
            route('hospital.delete', selectedHospital.id),
            {
                preserveScroll: true,
                onSuccess: () => {
                    setShowConfirm(false);
                    setSelectedHospital(null);
                },
            }
        );
    };

    /**
     * Cancel delete
     */
    const handleCancel = () => {
        setShowConfirm(false);
        setSelectedHospital(null);
    };

    /**
     * Flash messages
     */
    useEffect(() => {
        if (flash?.message?.success) {
            toast.success(flash.message.success);
        }

        if (flash?.message?.error) {
            toast.error(flash.message.error);
        }
    }, [flash]);


    /**
     * Table columns
     */
    const columns = [
        {
            key: '#',
            label: '#',
            render: (item) => item.id,
        },

        {
            key: 'hospital_name',
            label: 'Hospital Name',
            render: (item) => (
                <div className="flex items-center gap-3">
                    {item.hospital_logo ? (
                        <img
                            src={`${item.image_url ?? ''}/storage/${item.hospital_logo}`}
                            alt={item.hospital_name}
                            className="h-10 w-10 rounded-md border object-contain"
                        />
                    ) : (
                        <div className="flex h-10 w-10 items-center justify-center rounded-md border bg-muted">
                            <Hospital className="h-5 w-5 text-muted-foreground" />
                        </div>
                    )}

                    <div>
                        <div className="font-medium">
                            {item.hospital_name}
                        </div>

                        {item.address && (
                            <div className="max-w-[300px] truncate text-xs text-muted-foreground">
                                {item.address}
                            </div>
                        )}
                    </div>
                </div>
            ),
        },

        {
            key: 'mobile_number',
            label: 'Mobile',
            render: (item) =>
                item.mobile_number || '-',
        },

        {
            key: 'hospital_url',
            label: 'Website',
            render: (item) =>
                item.hospital_url ? (
                    <a
                        href={item.hospital_url}
                        target="_blank"
                        rel="noopener noreferrer"
                        className="text-primary hover:underline"
                    >
                        Visit Website
                    </a>
                ) : (
                    '-'
                ),
        },

        {
            key: 'status',
            label: 'Status',
            className: 'text-center',
            render: (item) => (
                <span
                    className={`badge ${item.status
                        ? 'badge--success'
                        : 'badge--warning'
                        }`}
                >
                    {item.status ? 'Active' : 'Inactive'}
                </span>
            ),
        },
        {
            key: 'verification_status',
            label: 'Verification Status',
            className: 'text-center',
            render: (item) => (
                <span
                    className={`badge ${item.verification_status === 'approved'
                        ? 'badge--success capitalize'
                        : 'badge--warning capitalize'
                        }`}
                >
                    {item.verification_status}
                </span>
            ),
        },
        {
            key: 'verification_url',
            label: 'Website',
            render: (item) =>
                item.verification_url ? (
                    <a
                        href={item.verification_url}
                        target="_blank"
                        rel="noopener noreferrer"
                        className="text-primary hover:underline"
                    >
                        Verify
                    </a>
                ) : (
                    '-'
                ),
        },
    ];

    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: 'Hospitals',
            href: route('hospital.index'),
        },
    ];
    console.log(hospital);
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Hospitals" />

            <ToastContainer />

            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">

                {/* Header / Search / Filters */}
                <PageHeader
                    data={data}
                    perPageItem={filters?.per_page}
                    handlePerPageChange={handlePerPageChange}
                    handleSearch={handleSearch}
                    placeholder="Search hospitals..."
                    handleReset={handleReset}
                    handleCreate={handleCreate}
                    canCreate={canCreate}
                />

                {/* Status Filter */}
                <div className="flex items-center gap-3">
                    <label
                        htmlFor="status"
                        className="text-sm font-medium"
                    >
                        Status
                    </label>

                    <select
                        id="status"
                        value={data.status}
                        onChange={handleStatusChange}
                        className="h-9 rounded-md border border-input bg-background px-3 py-1 text-sm shadow-sm focus:outline-none focus:ring-1 focus:ring-ring"
                    >
                        <option value="">
                            All
                        </option>

                        <option value="1">
                            Active
                        </option>

                        <option value="0">
                            Inactive
                        </option>
                    </select>
                </div>

                {/* Table */}
                <div className="relative overflow-x-auto shadow-md sm:rounded-lg">
                    <div className="inline-block min-w-full">
                        <Table
                            items={hospital.data}
                            columns={columns}
                            canEdit={canEdit}
                            canDelete={canDelete}
                            onEdit={handleEdit}
                            onDelete={handleDelete}
                            actionHeadClass="border p-4 w-24 text-center"
                            emptyMessage="No hospitals found"
                        />

                        {/* Pagination */}
                        <div className="mt-4 w-full">
                            <Pagination items={hospital.meta} />
                        </div>
                    </div>
                </div>
            </div>

            {/* Delete Confirmation */}
            <ConfirmDialog
                title="Delete Hospital"
                message={`Are you sure you want to delete this <b>${selectedHospital?.hospital_name ?? ''}</b>?`}
                onConfirm={handleConfirm}
                onCancel={handleCancel}
                isOpen={showConfirm}
            />
        </AppLayout>
    );
}
