import AppLayout from '@/layouts/app-layout';
import { Head, router, useForm } from '@inertiajs/react';
import Form from './Form';

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

export default function Edit({ doctor, specialties }) {
    const selectedSpecialties = doctor?.user?.specialties?.map((s) => s.id) || [];

    const { data, setData, post, errors, processing } = useForm<DoctorForm>({
        name: doctor.user.name || '',
        email: doctor.user.email || '',
        password: '',
        password_confirmation: '',
        phone: doctor.phone || '',
        gender: doctor.gender || '',
        dob: doctor.dob || '',
        specialization: doctor.specialization || '',
        designation: doctor.designation || '',
        registration_no: doctor.registration_no || '',
        working_institute: doctor.working_institute || '',
        qualification: doctor.qualification || '',
        experience_years: doctor.experience_years || '',
        bio: doctor.bio || '',
        social: doctor.social || { facebook: '', linkedin: '', twitter: '' },
        profile_image: null,
        active: doctor.active || boolean,
        featured: doctor.featured || false,
        specialization_ids: selectedSpecialties, // pre-fill selected specialties
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        post(route('doctors.update', doctor.uuid), { forceFormData: true });
    };

    const handleCancel = () => {
        router.get(route('doctors.index'));
    };

    return (
        <AppLayout>
            <Head title="Edit Doctor" />
            <div className="flex h-full flex-1 flex-col items-center gap-4 overflow-x-auto rounded-xl p-4">
                <Form
                    data={data}
                    setData={setData}
                    errors={errors}
                    processing={processing}
                    specialties={specialties}
                    handleSubmit={handleSubmit}
                    handleCancel={handleCancel}
                    submitBtnTitle="Update"
                    heading="Edit Doctor"
                    isUpdate={true}
                    initialImage={doctor?.user?.profile_image?.image_url}
                />
            </div>
        </AppLayout>
    );
}
