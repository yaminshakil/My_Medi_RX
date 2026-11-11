import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm, usePage } from '@inertiajs/react';
import { FormEventHandler, useEffect } from 'react';
import { ToastContainer, toast } from 'react-toastify';
import From from './Form';

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
    const { flash } = usePage().props;
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
        post(route('patients.profile.store'), {
            onError: (err) => console.log(err),
        });
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
                <From
                    data={data}
                    setData={setData}
                    errors={errors}
                    handleSubmit={handleSubmit}
                    processing={processing}
                    initialImage={null}
                    setShowConfirm={null}
                    is_delete={false}
                    submitTitle="Create"
                />
            </div>
        </AppLayout>
    );
}
