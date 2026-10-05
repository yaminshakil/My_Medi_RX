import { ConfirmDialog } from '@/Components/ConfirmDialog';
import PageHeader from '@/Components/PageHeader';
import { Pagination } from '@/Components/pagination';
import Table from '@/Components/Table';
import { Button } from '@/Components/ui/button';
import { Dialog, DialogContent, DialogHeader, DialogTitle } from '@/Components/ui/dialog';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Switch } from '@/Components/ui/switch';
import AppLayout from '@/Layouts/app-layout';
import { useCan } from '@/lib/can';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm, usePage } from '@inertiajs/react';
import { FormEventHandler, useEffect, useState } from 'react';
import { ToastContainer, toast } from 'react-toastify';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Medicine Durations',
        href: '/medicine-durations',
    },
];

export default function Index() {
    const { durations, flash, filters } = usePage().props;
    const [isOpen, setIsOpen] = useState(false);
    const [editId, setEditId] = useState(null);
    const [confirmDelete, setConfirmDelete] = useState(null);
    const canCreate = useCan('Create');
    const canEdit = useCan('Edit');
    const canDelete = useCan('Delete');

    const { data, setData, post, put, reset, errors, processing } = useForm({
        name: '',
        days: '',
        is_active: true,
    });

    const { data: searchData, setData: setSearchData } = useForm({
        search: filters?.search || '',
    });

    const openCreateModal = () => {
        reset();
        setEditId(null);
        setIsOpen(true);
    };

    const handleSubmit = (e) => {
        e.preventDefault();
        if (editId) {
            put(route('medicine-durations.update', editId), {
                onSuccess: () => setIsOpen(false),
            });
        } else {
            post(route('medicine-durations.store'), {
                onSuccess: () => setIsOpen(false),
            });
        }
    };

    useEffect(() => {
        if (flash.message.success) {
            toast.success(flash.message.success);
        }
        if (flash.message.error) {
            toast.error(flash.message.error);
        }
    }, [flash]);

    const handlePerPageChange = (e) => {
        router.get(route('medicine-durations.index'), { ...filters, per_page: e.target.value }, { preserveState: true });
    };

    const handleSearch = (e) => {
        setSearchData('search', e.target.value);
        router.get(route('medicine-durations.index'), { ...filters, search: e.target.value }, { preserveState: true });
    };

    const handleReset = () => {
        setSearchData('search', '');
        router.get(
            route('medicine-durations.index'),
            {},
            {
                preserveState: true,
                preserveScroll: true,
            },
        );
    };

    const handleEdit: FormEventHandler = (duration) => {
        setData({
            name: duration.name,
            days: duration.days ?? '',
            is_active: duration.is_active,
        });
        setEditId(duration.id);
        setIsOpen(true);
    };

    const handleDelete: FormEventHandler = (item) => {
        setConfirmDelete(item);
    };

    const handleConfirmDelete = (id) => {
        router.delete(route('medicine-durations.destroy', id), {
            onSuccess: () => setConfirmDelete(null),
        });
    };

    const columns = [
        { key: '#', label: '#', render: (item) => item.id },
        { key: 'name', label: 'Name' },
        { key: 'days', label: 'Days' },
        {
            key: 'is_active',
            label: 'Status',
            className: 'text-center',
            render: (item) => (
                <span className={`badge ${item.is_active ? 'badge--success' : 'badge--warning'}`}>{item.is_active ? 'Active' : 'Inactive'}</span>
            ),
        },
    ];

    const handleCreate = () => {
        openCreateModal();
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <ToastContainer />
            <Head title="Medicine Durations" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                {/* Header */}
                <PageHeader
                    data={searchData}
                    perPageItem={filters.per_page}
                    handlePerPageChange={handlePerPageChange}
                    handleSearch={handleSearch}
                    placeholder="Search Duration ..."
                    handleReset={handleReset}
                    handleCreate={handleCreate}
                    canCreate={canCreate}
                />

                <div className="relative overflow-x-auto shadow-md sm:rounded-lg">
                    <div className="inline-block min-w-full">
                        <Table
                            items={durations.data}
                            columns={columns}
                            canEdit={canEdit}
                            canDelete={canDelete}
                            onEdit={handleEdit}
                            onDelete={handleDelete}
                            actionHeadClass="border p-4 w-24 text-center"
                            emptyMessage="No Medicine Duration found."
                        />

                        {/* Pagination */}
                        {durations.links?.length > 0 && (
                            <div className="mt-4">
                                <Pagination items={durations} />
                            </div>
                        )}
                    </div>
                </div>

                {/* Form Modal */}
                <Dialog open={isOpen} onOpenChange={setIsOpen}>
                    <DialogContent aria-describedby="dialog-description">
                        <DialogHeader>
                            <DialogTitle>{editId ? 'Edit Duration' : 'Add New Duration'}</DialogTitle>
                        </DialogHeader>
                        <form
                            onSubmit={handleSubmit}
                            role="form"
                            aria-describedby="dialog-description"
                            className="w-full space-y-4 border p-4 shadow-md sm:rounded-lg"
                        >
                            <div>
                                <Label htmlFor="name">Name</Label>
                                <Input id="name" value={data.name} onChange={(e) => setData('name', e.target.value)} placeholder="e.g. 5 days" />
                                {errors.name && <div className="mt-1 text-sm text-red-600">{errors.name}</div>}
                            </div>

                            <div>
                                <Label htmlFor="days">Days (optional)</Label>
                                <Input
                                    id="days"
                                    type="number"
                                    value={data.days}
                                    onChange={(e) => setData('days', e.target.value)}
                                    placeholder="e.g. 5"
                                />
                                {errors.days && <div className="mt-1 text-sm text-red-600">{errors.days}</div>}
                            </div>

                            <div className="flex items-center space-x-2">
                                <Switch
                                    className="data-[state=checked]:bg-[var(--btn-base-color)]"
                                    checked={data.is_active}
                                    onCheckedChange={(val) => setData('is_active', val)}
                                />
                                <Label>Active</Label>
                            </div>

                            <div className="flex justify-end space-x-2 pt-3">
                                <Button type="button" onClick={() => setIsOpen(false)}>
                                    Cancel
                                </Button>
                                <Button type="submit" disabled={processing}>
                                    {editId ? 'Update' : 'Create'}
                                </Button>
                            </div>
                        </form>
                    </DialogContent>
                </Dialog>
            </div>
            {confirmDelete && (
                <ConfirmDialog
                    isOpen={!!confirmDelete}
                    title="Delete Duration"
                    message={`Are you sure you want to delete <b>${confirmDelete.name}</b>?`}
                    onConfirm={() => handleConfirmDelete(confirmDelete.id)}
                    onCancel={() => setConfirmDelete(null)}
                />
            )}
        </AppLayout>
    );
}
