import { AdminPageHeader } from '@/Components/AdminPageHeader';
import AppLayout from '@/Layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/react';
import { useEffect } from 'react';
import Form from './Form';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Edit menu',
        href: '/menus',
    },
];

export default function Edit(props) {
    const { data, setData, post, processing, errors, reset } = useForm({
        name: props.editmenu.name,
        slug: props.editmenu.slug,
        order_by: props.editmenu.order_by,
        menu_method: props.editmenu.menu_method,
        menu_icon: props.editmenu.menu_icon,
        role: props.role,
        parent_id: props.editmenu.parent_id,
    });

    useEffect(() => {
        return () => {
            reset('name', 'slug');
        };
    }, []);

    const handleSubmit = (e) => {
        e.preventDefault();
        post(route('menus.update', [props.editmenu.id]));
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Edit Menu" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl px-4">
                <AdminPageHeader breadcrumbs={breadcrumbs} description="Update an existing navigation menu item" />
                <div className="flex h-full flex-1 flex-col items-center gap-4 overflow-x-auto rounded-xl p-4">
                    <Form
                        data={data}
                        setData={setData}
                        handleSubmit={handleSubmit}
                        processing={processing}
                        errors={errors}
                        submitTitle="Update"
                        roles={props.roles}
                        parentmenus={props.parentmenus}
                        heading="Edit Menu"
                    />
                </div>
            </div>
        </AppLayout>
    );
}
