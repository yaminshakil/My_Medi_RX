import { ConfirmDialog } from '@/components/ConfirmDialog';
import PageHeader from '@/components/PageHeader';
import { Pagination } from '@/components/pagination';
import Table from '@/components/Table';
import AppLayout from '@/layouts/app-layout';
import { useCan } from '@/lib/can';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm, usePage } from '@inertiajs/react';
import { FormEventHandler, useEffect, useState } from 'react';
import { ToastContainer, toast } from 'react-toastify';
import { AppPageProps } from '@/types';


export default function Index({ specialties }) {
    const { translations } = usePage<AppPageProps>().props;
    const { flash, filters } = usePage().props;
    const [showConfirm, setShowConfirm] = useState(false);
    const [specialty, setSpecialty] = useState(false);
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
        router.get(route('medical-specialties.index'), queryString, {
            preserveState: true,
            preserveScroll: true,
        });
    };

    const handleReset = () => {
        setData('search', '');
        router.get(
            route('medical-specialties.index'),
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
        router.delete(route('medical-specialties.destroy', specialty.id), {
            preserveScroll: true,
        });
        setShowConfirm(false);
    };

    const handleCancel = () => {
        setShowConfirm(false);
    };

    const handlePerPageChange = (e) => {
        router.get(route('medical-specialties.index'), { ...filters, per_page: e.target.value }, { preserveState: true });
    };

    const handleEdit: FormEventHandler = (item) => {
        router.get(route('medical-specialties.edit', item.id));
    };

    const handleDelete: FormEventHandler = (item) => {
        setSpecialty(item);
        setShowConfirm(true);
    };

    const columns = [
        { key: '#', label: '#', render: (item) => item.id },
        { key: 'name', label: 'Name', render: (item) => item.translated_name ? item.translated_name : '-' },
        { key: 'description', label: 'Description', render: (item) => item.translated_description ? item.translated_description : '-' },
        { key: 'Parent', label: 'Parent', render: (item) => item.parent?.name ?? '-' },
        {
            key: 'is_surgical',
            label: 'Surgical?',
            className: 'text-center',
            render: (item) => (
                <span className={`badge ${item.is_surgical ? 'badge--success' : 'badge--warning'}`}>{item.is_surgical ? 'Yes' : 'No'}</span>
            ),
        },
    ];

    const handleCreate = () => {
        router.visit(route('medical-specialties.create'));
    };



    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: translations.common.medical_specialties,
            href: '/dashboard',
        },
    ];

    console.log(specialties);
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={translations.common.medical_specialties} />
            <ToastContainer />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <PageHeader
                    data={data}
                    perPageItem={filters.per_page}
                    handlePerPageChange={handlePerPageChange}
                    handleSearch={handleSearch}
                    placeholder={translations.common.search_specialty}
                    handleReset={handleReset}
                    handleCreate={handleCreate}
                    canCreate={canCreate}
                />

                <div className="relative overflow-x-auto shadow-md sm:rounded-lg">
                    <div className="inline-block min-w-full">
                        <Table
                            items={specialties.data}
                            columns={columns}
                            canEdit={canEdit}
                            canDelete={canDelete}
                            onEdit={handleEdit}
                            onDelete={handleDelete}
                            actionHeadClass="border p-4 w-24 text-center"
                            emptyMessage={translations.common.no_specialty_found}
                        />
                        <div className="mt-4 w-full">
                            <Pagination items={specialties} />
                        </div>
                    </div>
                </div>
            </div>
            <ConfirmDialog
                title={translations.common.delete_specialty}
                message={`Are you sure you want to delete this <b>${specialty?.name}</b>?`}
                onConfirm={handleConfirm}
                onCancel={handleCancel}
                isOpen={showConfirm}
            />
        </AppLayout>
    );
}
