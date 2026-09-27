import { ConfirmDialog } from '@/Components/ConfirmDialog';
import { Button } from '@/Components/ui/button';
import AppLayout from '@/Layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, router, usePage } from '@inertiajs/react';
import { ArrowLeft, Database, Download, Trash2 } from 'lucide-react';
import { useEffect, useState } from 'react';
import { ToastContainer, toast } from 'react-toastify';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Database Backup',
        href: route('settings.system.backup.index'),
    },
];

export default function Index() {
    const { flash, files } = usePage().props;
    const [showConfirm, setShowConfirm] = useState(false);
    const [item, setItem] = useState('');

    useEffect(() => {
        if (flash.message.success) {
            toast.success(flash.message.success);
        }
        if (flash.message.error) {
            toast.error(flash.message.error);
        }
    }, [flash]);

    const handleBackup = () => {
        router.post(route('settings.system.backup.database'));
    };

    const downloadBackup = (item) => {
        if (!item) return;

        // Direct browser navigation triggers download
        window.location.href = route('settings.system.backup.database.download', { item: item });
    };

    const handleDelete = () => {
        router.delete(route('settings.system.backup.database.delete', { item }), {
            onSuccess: () => {
                setShowConfirm(false);
            },
        });
    };

    const handleCancel = () => {
        setShowConfirm(false);
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Database Backup" />
            <ToastContainer />
            <div className="mx-auto w-1/2">
                <div className="mb-4 flex flex-col items-center justify-between gap-4 md:flex-row">
                    <div>
                        <Button onClick={() => window.history.back()} className="text-white">
                            <ArrowLeft className="h-4 w-4" /> Back
                        </Button>
                    </div>
                    <div>
                        <Button onClick={handleBackup} className="text-white">
                            <Database className="h-4 w-4 text-white" /> Backup Database
                        </Button>
                    </div>
                </div>
                <div className="relative overflow-x-auto shadow-md sm:rounded-lg">
                    <div className="inline-block min-w-full">
                        <table className="w-full border text-left text-sm text-gray-500 rtl:text-right dark:text-gray-400">
                            <thead className="bg-[var(--base-color)] text-xs text-gray-700 uppercase dark:bg-gray-700 dark:text-gray-400">
                                <tr className="bg-[var(--base-color)]">
                                    <th className="border p-4 text-white">Name</th>
                                    <th className="border p-4 text-white">Date</th>
                                    <th className="border p-4 text-center text-white">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                {files.map((item, index) => {
                                    // Example: backup_2025-12-03_15-30-00.sql
                                    const match = item.match(/(\d{4}-\d{2}-\d{2})/); // extract YYYY-MM-DD
                                    const date = match ? match[1] : 'Unknown';
                                    return (
                                        <tr key={index}>
                                            <td className="border p-4">{item}</td>
                                            <td className="border p-4">{date}</td>
                                            <td className="border p-4 align-middle">
                                                <div className="flex items-center justify-center gap-2">
                                                    <Button
                                                        type="button"
                                                        onClick={() => {
                                                            downloadBackup(item);
                                                        }}
                                                        className="text-white"
                                                    >
                                                        <Download className="h-4 w-4 text-white" /> Download
                                                    </Button>
                                                    <Button
                                                        variant="destructive"
                                                        type="button"
                                                        onClick={() => {
                                                            setShowConfirm(true);
                                                            setItem(item);
                                                        }}
                                                        className="text-white"
                                                    >
                                                        <Trash2 className="h-4 w-4 text-white" /> Delete
                                                    </Button>
                                                </div>
                                            </td>
                                        </tr>
                                    );
                                })}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <ConfirmDialog
                title="Delete File"
                message="Are you sure you want to delete this Backup File?"
                onConfirm={handleDelete}
                onCancel={handleCancel}
                isOpen={showConfirm}
            />
        </AppLayout>
    );
}
