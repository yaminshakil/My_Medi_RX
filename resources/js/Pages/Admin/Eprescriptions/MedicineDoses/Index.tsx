import { CommonModal } from '@/Components/CommonModal';
import { ConfirmDialog } from '@/Components/ConfirmDialog';
import PageHeader from '@/Components/PageHeader';
import { Pagination } from '@/Components/pagination';
import Table from '@/Components/Table';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Textarea } from '@/Components/ui/textarea';
import AppLayout from '@/Layouts/app-layout';
import { useCan } from '@/lib/can';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm, usePage } from '@inertiajs/react';
import { FormEventHandler, useEffect, useState } from 'react';
import { ToastContainer, toast } from 'react-toastify';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Medicine Doses',
        href: '/medicine-doses',
    },
];

export default function Index() {
    const { doses, flash, filters } = usePage().props;
    const [showForm, setShowForm] = useState(false);
    const [editingDose, setEditingDose] = useState(null);
    const [confirmDelete, setConfirmDelete] = useState(null);
    const canCreate = useCan('Create');
    const canEdit = useCan('Edit');
    const canDelete = useCan('Delete');

    const form = useForm({
        name: '',
        description: '',
        active: true,
    });

    const { data, setData } = useForm({
        search: filters?.search || '',
    });

    const openCreateForm = () => {
        setEditingDose(null);
        form.reset();
        setShowForm(true);
    };

    const openEditForm = (dose) => {
        setEditingDose(dose);
        form.setData({
            name: dose.name || '',
            description: dose.description || '',
            active: dose.active,
        });
        setShowForm(true);
    };

    const handleSubmit = (e) => {
        e.preventDefault();

        if (editingDose) {
            form.put(route('medicine-doses.update', editingDose.id), {
                onSuccess: () => {
                    setShowForm(false);
                    setEditingDose(null);
                },
            });
        } else {
            form.post(route('medicine-doses.store'), {
                onSuccess: () => {
                    setShowForm(false);
                    form.reset();
                },
            });
        }
    };

    const handleConfirmDelete = (id) => {
        router.delete(route('medicine-doses.destroy', id), {
            onSuccess: () => setConfirmDelete(null),
        });
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
        router.get(route('medicine-doses.index'), { ...filters, per_page: e.target.value }, { preserveState: true });
    };

    const handleSearch = (e) => {
        setData('search', e.target.value);
        router.get(route('medicine-doses.index'), { ...filters, search: e.target.value }, { preserveState: true });
    };

    const handleReset = () => {
        setData('search', '');
        router.get(
            route('medicine-doses.index'),
            {},
            {
                preserveState: true,
                preserveScroll: true,
            },
        );
    };

    const handleDoseCancel = () => {
        setShowForm(false);
    };

    const handleEdit: FormEventHandler = (item) => {
        openEditForm(item);
    };

    const handleDelete: FormEventHandler = (item) => {
        setConfirmDelete(item);
    };

    const columns = [
        { key: '#', label: '#', render: (item) => item.id },
        { key: 'name', label: 'Name' },
        { key: 'description', label: 'Description' },
        {
            key: 'active',
            label: 'Status',
            className: 'text-center',
            render: (item) => (
                <span className={`badge ${item.active ? 'badge--success' : 'badge--warning'}`}>{item.active ? 'Active' : 'Inactive'}</span>
            ),
        },
    ];

    const handleCreate = () => {
        openCreateForm();
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Drug Doses" />
            <ToastContainer />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                {/* Header */}
                <PageHeader
                    data={data}
                    perPageItem={filters.per_page}
                    handlePerPageChange={handlePerPageChange}
                    handleSearch={handleSearch}
                    placeholder="Search Doses ..."
                    handleReset={handleReset}
                    handleCreate={handleCreate}
                    canCreate={canCreate}
                />

                {/* Table */}
                <div className="relative overflow-x-auto shadow-md sm:rounded-lg">
                    <div className="inline-block min-w-full">
                        <Table
                            items={doses.data}
                            columns={columns}
                            canEdit={canEdit}
                            canDelete={canDelete}
                            onEdit={handleEdit}
                            onDelete={handleDelete}
                            actionHeadClass="border p-4 w-24 text-center"
                            emptyMessage="No Medicine doses found."
                        />
                        <div className="mt-4">
                            <Pagination items={doses} />
                        </div>
                    </div>
                </div>

                {/* Form Modal */}
                <CommonModal
                    onConfirm={handleSubmit}
                    onCancel={handleDoseCancel}
                    isOpen={showForm}
                    onClose={() => setShowForm(false)}
                    title={editingDose ? 'Edit Dose' : 'Add Dose'}
                >
                    <div className="space-y-4">
                        <div>
                            <Label className="block text-sm font-medium">Name</Label>
                            <Input
                                type="text"
                                className="mt-1 w-full rounded border px-3 py-2 text-sm"
                                value={form.data.name}
                                onChange={(e) => form.setData('name', e.target.value)}
                            />
                            {form.errors.name && <div className="mt-1 text-xs text-red-600">{form.errors.name}</div>}
                        </div>

                        <div>
                            <Label className="block text-sm font-medium">Description</Label>
                            <Textarea
                                className="mt-1 w-full rounded border px-3 py-2 text-sm"
                                rows={2}
                                value={form.data.description}
                                onChange={(e) => form.setData('description', e.target.value)}
                            ></Textarea>
                        </div>

                        <div className="flex items-center space-x-2">
                            <Input
                                id="active"
                                className="w-6"
                                type="checkbox"
                                checked={form.data.active}
                                onChange={(e) => form.setData('active', e.target.checked)}
                            />
                            <Label htmlFor="active" className="text-sm">
                                Active
                            </Label>
                        </div>
                    </div>
                </CommonModal>
                {/* Confirm Delete Dialog */}
                {confirmDelete && (
                    <ConfirmDialog
                        isOpen={!!confirmDelete}
                        title="Delete Dose"
                        message={`Are you sure you want to delete <b>${confirmDelete.name}</b>?`}
                        onConfirm={() => handleConfirmDelete(confirmDelete.id)}
                        onCancel={() => setConfirmDelete(null)}
                    />
                )}
            </div>
        </AppLayout>
    );
}
