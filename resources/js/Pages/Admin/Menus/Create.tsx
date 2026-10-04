import { AdminPageHeader } from '@/Components/AdminPageHeader';
import AppLayout from '@/Layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm, usePage } from '@inertiajs/react';
import { FormEventHandler, useEffect } from 'react';
import Form from './Form';
import { AppPageProps } from '@/types';



export default function Create(props) {
    const { translations } = usePage<AppPageProps>().props;
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

    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: translations.common.create_menu,
            href: '/menus',
        },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={translations.common.create_menu} />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl px-4">
                <AdminPageHeader breadcrumbs={breadcrumbs} description={translations.common.create_menu_desc} />
                <div className="flex h-full flex-1 flex-col items-center gap-4 overflow-x-auto rounded-xl p-4">
                    <Form
                        data={data}
                        setData={setData}
                        handleSubmit={handleSubmit}
                        processing={processing}
                        errors={errors}
                        submitTitle={translations.common.create}
                        roles={props.roles}
                        parentmenus={props.parentmenus}
                        heading={translations.common.create_menu}
                    />
                </div>
            </div>
        </AppLayout>
    );
}
