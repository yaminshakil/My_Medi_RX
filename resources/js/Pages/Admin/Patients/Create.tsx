import AppLayout from '@/Layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/react';
import { FormEventHandler } from 'react';
import Form from './Form';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Patient Profile',
        href: '/pateint/profile',
    },
];

type PatientForm = {
    profile_image?: File | null;
    name: string;
    phone: string;
    email: string;
    gender: string;
    date_of_birth?: string;
    address: string;
    city: string;
    blood_group: string;
    marital_status: string;
};

export default function Create() {
    const { data, setData, post, errors, processing } = useForm<PatientForm>({
        name: '',
        phone: '',
        email: '',
        date_of_birth: '',
        address: '',
        city: '',
        gender: '',
        blood_group: '',
        profile_image: null,
        marital_status: '',
    });

    const handleSubmit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('patients.store'));
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Patient Profile" />
            <div className="flex h-full flex-1 flex-col items-center gap-4 overflow-x-auto rounded-xl p-4">
                <Form data={data} setData={setData} handleSubmit={handleSubmit} processing={processing} errors={errors} submitTitle="Create" />
            </div>
        </AppLayout>
    );
}
