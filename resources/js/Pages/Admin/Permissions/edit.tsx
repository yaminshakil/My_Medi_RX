import { AdminPageHeader } from '@/Components/AdminPageHeader';
import AppLayout from '@/Layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/react';
import { FormEventHandler } from 'react';
import Form from './Form';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Permissions', href: '/admin/permissions' },
    { title: 'Edit Permission', href: '' },
];

type PermissionForm = {
    name: string;
};

export default function Edit({ permission }) {
    const { data, setData, post, processing, errors } = useForm<Required<PermissionForm>>({
        name: permission.name,
        _method: 'PUT',
    });

    const handleSubmit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('permissions.update', permission.id));
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Edit Permission" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl px-4">
                <AdminPageHeader breadcrumbs={breadcrumbs} description="Update an existing system permission" />
                <div className="flex flex-1 flex-col mb-4">
                    <Form
                        data={data}
                        setData={setData}
                        errors={errors}
                        handleSubmit={handleSubmit}
                        processing={processing}
                        submitTitle="Update"
                        heading="Edit Permission"
                    />
                </div>
            </div>
        </AppLayout>
    );
}
