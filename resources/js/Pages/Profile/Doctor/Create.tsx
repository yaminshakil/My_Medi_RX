import AppLayout from '@/Layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm, usePage } from '@inertiajs/react';
import { FormEventHandler, useEffect } from 'react';
import { ToastContainer, toast } from 'react-toastify';
import From from './Form';

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Create Profile', href: '/doctor/profile' }];

type DoctorForm = {
    profile_image?: File | null;
    phone?: string;
    gender?: string;
    dob?: string;
    specialization?: string;
    working_institute: string;
    designation?: string;
    qualification?: string;
    registration_no: string;
    experience_years?: number | '';
    bio?: string;
    active: boolean;
};

export default function Create() {
    const { specialties, flash } = usePage().props;
    const { data, setData, post, processing, errors, reset } = useForm<DoctorForm>({
        profile_image: null,
        phone: '',
        gender: '',
        dob: '',
        specialization: '',
        working_institute: '',
        designation: '',
        registration_no: '',
        qualification: '',
        experience_years: '',
        bio: '',
        social: { facebook: '', linkedin: '', twitter: '' },
        active: true,
        featured: false,
        specialization_ids: [],
    });

    const handleSubmit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('doctor.profile.create'), {
            onFinish: () => reset('password', 'password_confirmation'),
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
            <Head title="Create Profile" />
            <ToastContainer />
            <div className="flex h-full flex-1 flex-col items-center gap-4 overflow-x-auto rounded-xl p-4">
                <From
                    data={data}
                    setData={setData}
                    errors={errors}
                    handleSubmit={handleSubmit}
                    processing={processing}
                    initialImage={null}
                    setShowConfirm={false}
                    is_delete={false}
                    submitTitle="Create"
                    specialties={specialties}
                />
            </div>
        </AppLayout>
    );
}
