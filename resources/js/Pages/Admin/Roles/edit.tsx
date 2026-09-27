import { AdminPageHeader } from '@/Components/AdminPageHeader';
import AppLayout from '@/Layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/react';
import { FormEventHandler } from 'react';
import Form from './Form';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Roles', href: '/admin/roles' },
    { title: 'Edit Role', href: '' },
];

type RoleForm = {
    name: string;
    permissions: [];
};

export default function Edit({ role, rolePermissions, permissions }) {
    const { data, setData, post, processing, errors } = useForm<Required<RoleForm>>({
        name: role.name,
        permissions: rolePermissions || [],
        _method: 'PUT',
    });

    const handleSubmit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('roles.update', role.id));
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Edit Role" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl px-4">
                <AdminPageHeader breadcrumbs={breadcrumbs} description="Update role name and assigned permissions" />
                <div className="flex flex-1 flex-col mb-4">
                    <Form
                        data={data}
                        setData={setData}
                        errors={errors}
                        handleSubmit={handleSubmit}
                        processing={processing}
                        permissions={permissions}
                        submitTitle="Update"
                        heading="Edit Role"
                    />
                </div>
            </div>
        </AppLayout>
    );
}
