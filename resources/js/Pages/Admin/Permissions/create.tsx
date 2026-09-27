import { AdminPageHeader } from '@/Components/AdminPageHeader';
import AppLayout from '@/Layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/react';
import { FormEventHandler } from 'react';
import Form from './Form';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Permissions', href: '/admin/permissions' },
    { title: 'Create Permission', href: '' },
];

type PermissionForm = {
    name: string;
};

export default function Create() {
    const { data, setData, post, processing, errors, reset } = useForm<Required<PermissionForm>>({
        name: '',
    });
    const handleSubmit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('permissions.store'), {
            onFinish: () => reset('name'),
        });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Create Permission" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl px-4">
                <AdminPageHeader breadcrumbs={breadcrumbs} description="Add a new system permission" />
                <div className="flex flex-1 flex-col mb-4">
                    <Form
                        data={data}
                        setData={setData}
                        errors={errors}
                        handleSubmit={handleSubmit}
                        processing={processing}
                        submitTitle="Create"
                        heading="Add New Permission"
                    />
                </div>
            </div>
        </AppLayout>
    );
}
