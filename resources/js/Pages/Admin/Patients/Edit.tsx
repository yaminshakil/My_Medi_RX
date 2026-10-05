import AppLayout from '@/Layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm, usePage } from '@inertiajs/react';
import { FormEventHandler, useEffect } from 'react';
import { ToastContainer, toast } from 'react-toastify';
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

export default function Edit() {
    const { patient, flash } = usePage().props;
    const { data, setData, post, errors, processing } = useForm<PatientForm>({
        name: patient.name || '',
        phone: patient.phone || '',
        email: patient.email || '',
        date_of_birth: patient.birth_date || '',
        address: patient.address || '',
        city: patient.city || '',
        gender: patient.gender || '',
        blood_group: patient.blood_group || '',
        profile_image: null,
        marital_status: patient.marital_status || '',
        _method: 'PUT',
    });

    const handleSubmit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('patients.update', patient.id));
    };

    useEffect(() => {
        if (flash.message.success) {
            toast.success(flash.message.success);
        }
        if (flash.message.error) {
            toast.error(flash.message.error);
        }
    }, [flash]);

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Patient Profile" />
            <ToastContainer />
            <div className="flex h-full flex-1 flex-col items-center gap-4 overflow-x-auto rounded-xl p-4">
                <Form
                    data={data}
                    setData={setData}
                    handleSubmit={handleSubmit}
                    processing={processing}
                    errors={errors}
                    submitTitle="Update"
                    initialImage={patient?.profile_image_url}
                />
            </div>
        </AppLayout>
    );
}
