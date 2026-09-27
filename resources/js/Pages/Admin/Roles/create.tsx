import { AdminPageHeader } from '@/Components/AdminPageHeader';
import AppLayout from '@/Layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/react';
import { FormEventHandler } from 'react';
import Form from './Form';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Roles', href: '/admin/roles' },
    { title: 'Create Role', href: '' },
];

type RoleForm = {
    name: string;
    permissions: [];
};

export default function Create({ permissions }) {
    const { data, setData, post, processing, errors, reset } = useForm<Required<RoleForm>>({
        name: '',
        permissions: [],
    });
    const handleSubmit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('roles.store'), {
            onFinish: () => reset('name'),
        });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Create Role" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl px-4">
                <AdminPageHeader breadcrumbs={breadcrumbs} description="Add a new user role with permissions" />
                <div className="flex flex-1 flex-col mb-4">
                    <Form
                        data={data}
                        setData={setData}
                        errors={errors}
                        handleSubmit={handleSubmit}
                        processing={processing}
                        permissions={permissions}
                        submitTitle="Create"
                        heading="Add New Role"
                    />
                </div>
            </div>
        </AppLayout>
    );
}
