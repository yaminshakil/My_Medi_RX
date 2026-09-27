import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm, usePage } from '@inertiajs/react';
import { FormEventHandler } from 'react';
import Form from './Form';

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Create Doctor', href: '/doctors/create' }];

type DoctorForm = {
    name: string;
    email: string;
    password: string;
    password_confirmation: string;
    profile_image?: File | null;
    phone?: string;
    gender?: string;
    dob?: string;
    registration_no: string;
    specialization?: string;
    working_institute: string;
    designation?: string;
    qualification?: string;
    experience_years?: number | '';
    bio?: string;
    active: boolean;
};

export default function Create() {
    const { specialties } = usePage().props;
    const { data, setData, post, processing, errors, reset } = useForm<DoctorForm>({
        name: '',
        email: '',
        password: '',
        password_confirmation: '',

        profile_image: null,
        phone: '',
        gender: '',
        dob: '',
        specialization: '',
        working_institute: '',
        registration_no: '',
        designation: '',
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
        post(route('doctors.store'), {
            onFinish: () => reset('password', 'password_confirmation'),
        });
    };

    const handleCancel = () => {
        router.get(route('doctors.index'));
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Create Doctor" />
            <div className="flex h-full flex-1 flex-col items-center gap-4 overflow-x-auto rounded-xl p-4">
                <Form
                    data={data}
                    setData={setData}
                    errors={errors}
                    processing={processing}
                    specialties={specialties}
                    handleSubmit={handleSubmit}
                    handleCancel={handleCancel}
                    submitBtnTitle="Create"
                    heading="Add New Doctor"
                    initialImage={null}
                />
            </div>
        </AppLayout>
    );
}
