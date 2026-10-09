import { ConfirmDialog } from '@/components/ConfirmDialog';
import PageHeader from '@/components/PageHeader';
import { Pagination } from '@/components/pagination';
import Table from '@/components/Table';
import { Button } from '@/components/ui/button';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import AppLayout from '@/layouts/app-layout';
import { useCan } from '@/lib/can';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm, usePage } from '@inertiajs/react';
import { Check, Star } from 'lucide-react';
import { FormEventHandler, useEffect, useState } from 'react';
import { ToastContainer, toast } from 'react-toastify';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Manage Cambers',
        href: '/chambers',
    },
];

export default function Index(props) {
    const { flash, filters } = usePage().props;
    const [showConfirm, setShowConfirm] = useState(false);
    const [chamber, setChamber] = useState(false);
    const canCreate = useCan('Create');
    const canEdit = useCan('Edit');
    const canDelete = useCan('Delete');

    const { data, setData } = useForm({
        search: filters?.search || '',
    });

    const handleSearch = (e) => {
        const value = e.target.value;
        setData('search', value);
        const queryString = value ? { search: value } : {};
        router.get(route('chambers.index'), queryString, {
            preserveState: true,
            preserveScroll: true,
        });
    };

    const handleReset = () => {
        setData('search', '');
        router.get(
            route('chambers.index'),
            {},
            {
                preserveState: true,
                preserveScroll: true,
            },
        );
    };

    const handleConfirm = () => {
        router.delete(route('chambers.destroy', chamber.id), {
            preserveScroll: true,
            onSuccess: () => {
                setShowConfirm(false);
            },
        });
    };

    const handleCancel = () => {
        setShowConfirm(false);
    };

    function Content({ content }) {
        return <div className="ql-editor text-center" dangerouslySetInnerHTML={{ __html: content }} />;
    }

    useEffect(() => {
        if (flash.message.success) {
            toast.success(flash.message.success);
        }
        if (flash.message.error) {
            toast.error(flash.message.error);
        }
    }, [flash]);

    const handleEdit: FormEventHandler = (item) => {
        router.get(route('chambers.edit', item.id));
    };

    const handleDelete: FormEventHandler = (item) => {
        setChamber(item);
        setShowConfirm(true);
    };

    const columns = [
        { key: '#', label: '#', render: (item) => item.id },
        { key: 'name', label: 'Name' },
        { key: 'header_left', label: 'Prescription Header Left', render: (item) => <Content content={item.header_left} /> },
        { key: 'header_right', label: 'Prescription Header Rright', render: (item) => <Content content={item.header_right} /> },
        { key: 'footer_info', label: 'Prescription Footer', render: (item) => <Content content={item.footer_info} /> },
        {
            key: 'active',
            label: 'Status',
            className: 'text-center',
            render: (item) =>
                item.is_active ? (
                    <TooltipProvider>
                        <Tooltip>
                            <TooltipTrigger asChild>
                                <span className="inline-flex cursor-default items-center gap-1 rounded-full border border-green-300 bg-green-100 px-3 py-1 text-xs font-semibold text-green-700">
                                    <Check size={14} className="text-green-600" />
                                    Active
                                </span>
                            </TooltipTrigger>
                            <TooltipContent>
                                <p>This is your default chamber</p>
                            </TooltipContent>
                        </Tooltip>
                    </TooltipProvider>
                ) : (
                    <div className="flex flex-col items-center justify-center gap-2 md:flex-row">
                        <span className="inline-flex items-center rounded-full border border-yellow-300 bg-yellow-100 px-3 py-1 text-xs font-semibold text-yellow-700">
                            Inactive
                        </span>
                        <TooltipProvider>
                            <Tooltip>
                                <TooltipTrigger asChild>
                                    <Button
                                        size="sm"
                                        className="flex items-center gap-1 rounded-full px-3 py-1 text-xs text-white"
                                        onClick={() => router.get(route('chambers.setDefault', item.id))}
                                    >
                                        <Star size={14} />
                                        Set Default
                                    </Button>
                                </TooltipTrigger>
                                <TooltipContent>
                                    <p>Set this chamber as the default active one</p>
                                </TooltipContent>
                            </Tooltip>
                        </TooltipProvider>
                    </div>
                ),
        },
    ];

    const handleCreate = () => {
        router.get(route('chambers.create'));
    };

    const handlePerPageChange = (e) => {
        router.get(route('chambers.index'), { ...filters, per_page: e.target.value }, { preserveState: true });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="All Chambers" />
            <ToastContainer />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <PageHeader
                    data={data}
                    perPageItem={filters.per_page}
                    handlePerPageChange={handlePerPageChange}
                    handleSearch={handleSearch}
                    placeholder="Search Chamber ..."
                    handleReset={handleReset}
                    handleCreate={handleCreate}
                    canCreate={canCreate}
                    CreateBtn="Add New"
                />
                <div className="relative overflow-x-auto shadow-md sm:rounded-lg">
                    <div className="inline-block min-w-full">
                        <Table
                            items={props.doctorChambers.data}
                            columns={columns}
                            canEdit={canEdit}
                            canDelete={canDelete}
                            onEdit={handleEdit}
                            onDelete={handleDelete}
                            actionHeadClass="border p-4 w-24 text-center"
                            emptyMessage="No Chamber found."
                        />
                        <div className="mt-4">
                            <Pagination items={props.doctorChambers} />
                        </div>
                    </div>
                </div>
                <ConfirmDialog
                    title="Delete Chamber"
                    message={`Are you sure you want to delete this <b>${chamber?.name}</b> chamber?`}
                    onConfirm={handleConfirm}
                    onCancel={handleCancel}
                    isOpen={showConfirm}
                />
            </div>
        </AppLayout>
    );
}
