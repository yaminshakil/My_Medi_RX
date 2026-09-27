import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/react';
import { FormEventHandler } from 'react';
import Form from './Form';

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Create Medical Specialty', href: '/medical-specialties/create' }];

type SpecialtyForm = {
    name: string;
    icon: string;
    description: string;
    parent_id?: number | '';
    is_surgical: boolean;
};

export default function Create({ parents }) {
    const { data, setData, post, processing, errors } = useForm<SpecialtyForm>({
        name: '',
        icon: '',
        description: '',
        parent_id: null,
        is_surgical: false,
    });

    const handleSubmit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('medical-specialties.store'));
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Create Medical Specialty" />
            <div className="flex h-full flex-1 flex-col items-center gap-4 overflow-x-auto rounded-xl p-4">
                <Form
                    parents={parents}
                    data={data}
                    setData={setData}
                    handleSubmit={handleSubmit}
                    processing={processing}
                    errors={errors}
                    submitTitle="Create"
                    heading="Add Medical Specialty"
                />
            </div>
        </AppLayout>
    );
}
