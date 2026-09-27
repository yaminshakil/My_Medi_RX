import { ConfirmDialog } from '@/Components/ConfirmDialog';
import Table from '@/Components/Table';
import { Button } from '@/Components/ui/button';
import AppLayout from '@/Layouts/app-layout';
import { useCan } from '@/lib/can';
import { type BreadcrumbItem } from '@/types';
import { Head, router, usePage } from '@inertiajs/react';
import { ArrowLeft } from 'lucide-react';
import { FormEventHandler, useEffect, useState } from 'react';
import { ToastContainer, toast } from 'react-toastify';

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Notifications', href: '/settings/email-templates' }];

export default function Index({ templates }) {
    const { flash } = usePage().props;
    const [showConfirm, setShowConfirm] = useState(false);
    const [template, setTemplate] = useState(false);
    const canEdit = useCan('Edit');
    const canDelete = useCan('Delete');

    const handleConfirm = () => {
        router.delete(route('email-templates.destroy', template.id), {
            preserveScroll: true,
            onSuccess: () => setShowConfirm(false),
        });
    };

    const handleCancel = () => {
        setShowConfirm(false);
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
        router.get(route('email-templates.edit', item.id));
    };

    const handleDelete: FormEventHandler = (item) => {
        setTemplate(item);
        setShowConfirm(true);
    };

    const columns = [
        { key: '#', label: '#', render: (item) => item.id },
        { key: 'name', label: 'Name' },
        { key: 'slug', label: 'Slug' },
        { key: 'subject', label: 'Subject' },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Email Templates" />
            <ToastContainer />
            <div className="ml-auto p-4">
                <Button onClick={() => window.history.back()} className="text-white">
                    <ArrowLeft className="h-4 w-4" /> Back
                </Button>
            </div>
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div className="flex items-center justify-between">
                    <h1 className="text-2xl font-bold">Email Templates</h1>
                </div>

                <div className="relative overflow-x-auto shadow-md sm:rounded-lg">
                    <Table
                        items={templates}
                        columns={columns}
                        canEdit={canEdit}
                        canDelete={canDelete}
                        onEdit={handleEdit}
                        onDelete={handleDelete}
                        actionHeadClass="border p-4 w-24 text-center"
                        emptyMessage="No Template found."
                    />
                </div>
            </div>
            <ConfirmDialog
                title="Delete Template"
                message="Are you sure you want to delete this Template?"
                onConfirm={handleConfirm}
                onCancel={handleCancel}
                isOpen={showConfirm}
            />
        </AppLayout>
    );
}
