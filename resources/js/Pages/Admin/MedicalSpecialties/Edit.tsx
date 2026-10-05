import AppLayout from '@/Layouts/app-layout';
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

export default function Edit({ specialty, parents }) {
    const { data, setData, put, processing, errors } = useForm<SpecialtyForm>({
        name: specialty?.name || '',
        icon: specialty?.icon || '',
        description: specialty?.description || '',
        parent_id: specialty?.parent_id || null,
        is_surgical: specialty?.is_surgical || false,
    });

    const handleSubmit: FormEventHandler = (e) => {
        e.preventDefault();
        put(route('medical-specialties.update', specialty?.id));
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Edit Medical Specialty" />
            <div className="flex h-full flex-1 flex-col items-center gap-4 overflow-x-auto rounded-xl p-4">
                <Form
                    parents={parents}
                    data={data}
                    setData={setData}
                    handleSubmit={handleSubmit}
                    processing={processing}
                    errors={errors}
                    submitTitle="Update"
                    heading="Edit Medical Specialty"
                />
            </div>
        </AppLayout>
    );
}
