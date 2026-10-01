import { ConfirmDialog } from '@/components/ConfirmDialog';
import PageHeader from '@/components/PageHeader';
import { Pagination } from '@/components/pagination';
import Table from '@/components/Table';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/app-layout';
import { useCan } from '@/lib/can';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm, usePage } from '@inertiajs/react';
import { FormEventHandler, useEffect, useState } from 'react';
import { ToastContainer, toast } from 'react-toastify';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Manufacturers',
        href: '/manufacturers',
    },
];

export default function Index() {
    const { manufacturers, flash, filters } = usePage().props;
    const [open, setOpen] = useState(false);
    const [editing, setEditing] = useState(null);
    const canCreate = useCan('Create');
    const canEdit = useCan('Edit');
    const canDelete = useCan('Delete');
    const [confirmDelete, setConfirmDelete] = useState(null);

    const form = useForm({
        company_name: '',
        is_active: 1,
    });

    const { data, setData } = useForm({
        search: filters?.search || '',
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        if (editing) {
            form.put(route('manufacturers.update', editing.id), {
                onSuccess: () => {
                    setOpen(false);
                    setEditing(null);
                    form.reset();
                },
            });
        } else {
            form.post(route('manufacturers.store'), {
                onSuccess: () => {
                    setOpen(false);
                    form.reset();
                },
            });
        }
    };

    const handlePerPageChange = (e) => {
        router.get(route('manufacturers.index'), { ...filters, per_page: e.target.value }, { preserveState: true });
    };

    const handleSearch = (e) => {
        setData('search', e.target.value);
        router.get(route('manufacturers.index'), { ...filters, search: e.target.value }, { preserveState: true });
    };

    const handleReset = () => {
        setData('search', '');
        router.get(
            route('manufacturers.index'),
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

    const handleEdit: FormEventHandler = (item) => {
        setEditing(item);
        form.setData({
            company_name: item.company_name,
            is_active: item.is_active,
        });
        setOpen(true);
    };

    const handleDelete: FormEventHandler = (item) => {
        setConfirmDelete(item);
    };

    const handleConfirmDelete = (id) => {
        router.delete(route('manufacturers.destroy', id), {
            onSuccess: () => setConfirmDelete(null),
        });
    };

    const columns = [
        { key: '#', label: '#', render: (item) => item.id },
        { key: 'company_name', label: 'Company Name', render: (item) => item.company_name },
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
        setOpen(true);
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Drug Manufacturers" />
            <ToastContainer />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                {/* Header */}
                <PageHeader
                    data={data}
                    perPageItem={filters.per_page}
                    handlePerPageChange={handlePerPageChange}
                    handleSearch={handleSearch}
                    placeholder="Search Company Name ..."
                    handleReset={handleReset}
                    handleCreate={handleCreate}
                    canCreate={canCreate}
                />
                <div className="relative overflow-x-auto shadow-md sm:rounded-lg">
                    <div className="inline-block min-w-full">
                        <Table
                            items={manufacturers.data}
                            columns={columns}
                            canEdit={canEdit}
                            canDelete={canDelete}
                            onEdit={handleEdit}
                            onDelete={handleDelete}
                            actionHeadClass="border p-4 w-24 text-center"
                            emptyMessage="No Manufacturer found."
                        />

                        {/* Pagination */}
                        <div className="mt-4">
                            <Pagination items={manufacturers} />
                        </div>
                    </div>
                </div>

                {/* Modal */}
                <Dialog open={open} onOpenChange={setOpen}>
                    <DialogContent>
                        <DialogHeader>
                            <DialogTitle>{editing ? 'Edit Manufacturer' : 'Add Manufacturer'}</DialogTitle>
                        </DialogHeader>
                        <form onSubmit={handleSubmit} role="form" className="w-full space-y-4 border p-4 shadow-md sm:rounded-lg">
                            <div>
                                <Label className="mb-1 block text-sm">Company Name</Label>
                                <Input
                                    type="text"
                                    value={form.data.company_name}
                                    onChange={(e) => form.setData('company_name', e.target.value)}
                                    className="w-full rounded border p-2"
                                />
                                {form.errors.company_name && <div className="text-sm text-red-500">{form.errors.company_name}</div>}
                            </div>
                            <div className="flex items-center space-x-2">
                                <Input
                                    id="active"
                                    className="w-6"
                                    type="checkbox"
                                    checked={form.data.is_active}
                                    onChange={(e) => form.setData('is_active', e.target.checked)}
                                />
                                <Label htmlFor="active" className="text-sm">
                                    Active
                                </Label>
                            </div>
                            <div className="flex justify-start space-x-2">
                                <Button
                                    type="button"
                                    onClick={() => {
                                        setOpen(false);
                                        setEditing(null);
                                    }}
                                >
                                    Cancel
                                </Button>
                                <Button type="submit" disabled={form.processing}>
                                    {editing ? 'Update' : 'Create'}
                                </Button>
                            </div>
                        </form>
                    </DialogContent>
                </Dialog>
            </div>
            {confirmDelete && (
                <ConfirmDialog
                    isOpen={!!confirmDelete}
                    title="Delete Manufacturer"
                    message={`Are you sure you want to delete <b>${confirmDelete.company_name}</b>?`}
                    onConfirm={() => handleConfirmDelete(confirmDelete.id)}
                    onCancel={() => setConfirmDelete(null)}
                />
            )}
        </AppLayout>
    );
}
