import { AdminPageHeader } from '@/Components/AdminPageHeader';
import AppLayout from '@/Layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/react';
import { FormEventHandler, useEffect } from 'react';
import Form from './Form';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Create menu',
        href: '/menus',
    },
];

export default function Create(props) {
    const { data, setData, post, processing, errors, reset } = useForm({
        name: '',
        slug: '',
        role: '',
        parent_id: '',
    });

    useEffect(() => {
        return () => {
            reset('name', 'slug');
        };
    }, []);

    const handleSubmit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('menus.create'));
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Create Menu" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl px-4">
                <AdminPageHeader breadcrumbs={breadcrumbs} description="Add a new navigation menu item" />
                <div className="flex h-full flex-1 flex-col items-center gap-4 overflow-x-auto rounded-xl p-4">
                    <Form
                        data={data}
                        setData={setData}
                        handleSubmit={handleSubmit}
                        processing={processing}
                        errors={errors}
                        submitTitle="Create"
                        roles={props.roles}
                        parentmenus={props.parentmenus}
                        heading="Create Menu"
                    />
                </div>
            </div>
        </AppLayout>
    );
}
