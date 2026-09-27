import { AdminPageHeader } from '@/Components/AdminPageHeader';
import AppLayout from '@/Layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/react';
import { FormEventHandler } from 'react';
import Form from './Form';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Users', href: '/admin/users' },
    { title: 'Edit User', href: '' },
];

type UserForm = {
    name: string;
    email: string;
    password: string;
    password_confirmation: string;
    roles: [];
};

export default function Edit({ user, userRoles, roles }) {
    const { data, setData, post, processing, errors, reset } = useForm<Required<UserForm>>({
        name: user.name,
        email: user.email,
        password: '',
        password_confirmation: '',
        roles: userRoles || [],
        _method: 'PUT',
    });

    const handleSubmit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('users.update', user.uuid), {
            onFinish: () => reset('password', 'password_confirmation'),
        });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Edit User" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl px-4">
                <AdminPageHeader breadcrumbs={breadcrumbs} description="Update user account details and roles" />
                <div className="flex flex-1 flex-col mb-4">
                    <Form
                        data={data}
                        setData={setData}
                        errors={errors}
                        handleSubmit={handleSubmit}
                        processing={processing}
                        roles={roles}
                        submitTitle="Update"
                        heading="Edit User"
                        isUpdate={true}
                    />
                </div>
            </div>
        </AppLayout>
    );
}
