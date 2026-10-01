import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm, usePage } from '@inertiajs/react';
import { FormEventHandler } from 'react';
import Form from './Form';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Create Medicine',
        href: '/medicines',
    },
];

export default function Create() {
    const { manufacturers } = usePage().props;
    const { data, setData, post, processing, errors } = useForm({
        doctor_id: '',
        brand_name: '',
        generic_name: '',
        strength: '',
        type: '',
        manufacturer_id: '',
        is_active: 1,
    });

    const handleSubmit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('medicines.store'));
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Add Medicine" />
            <div className="flex h-full flex-1 flex-col items-center gap-4 overflow-x-auto rounded-xl p-4">
                <Form
                    data={data}
                    setData={setData}
                    handleSubmit={handleSubmit}
                    errors={errors}
                    processing={processing}
                    manufacturers={manufacturers}
                    heading="Add Medicine"
                    submitTitle="Create"
                />
            </div>
        </AppLayout>
    );
}
