import { ConfirmDialog } from '@/Components/ConfirmDialog';
import AppLayout from '@/Layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm, usePage } from '@inertiajs/react';
import { useEffect, useState } from 'react';
import { ToastContainer, toast } from 'react-toastify';
import From from './Form';

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Edit Profile', href: '/doctor/profile' }];

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

export default function Edit() {
    const { doctor, specialties, flash } = usePage().props;
    const [showConfirm, setShowConfirm] = useState(false);

    const selectedSpecialties = doctor?.user?.specialties?.map((s) => s.id) || [];

    const { data, setData, post, errors, processing } = useForm<DoctorForm>({
        phone: doctor.phone || '',
        gender: doctor.gender || '',
        dob: doctor.dob || '',
        specialization: doctor.specialization || '',
        working_institute: doctor.working_institute || '',
        designation: doctor.designation || '',
        qualification: doctor.qualification || '',
        registration_no: doctor.registration_no || '',
        experience_years: doctor.experience_years || '',
        bio: doctor.bio || '',
        social: doctor.social || { facebook: '', linkedin: '', twitter: '' },
        profile_image: null,
        active: doctor.active || boolean,
        specialization_ids: selectedSpecialties, // pre-fill selected specialties
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        post(route('doctor.profile.update'));
    };

    useEffect(() => {
        if (flash.message.success) {
            toast.success(flash.message.success);
        }
        if (flash.message.error) {
            toast.error(flash.message.error);
        }
    }, [flash]);

    const handleConfirm = () => {
        router.delete(route('doctors.destroy', doctor), {
            preserveScroll: true,
            onSuccess: () => {
                setShowConfirm(false);
            },
        });
    };

    const handleCancel = () => {
        setShowConfirm(false);
    };
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Edit Profile" />
            <ToastContainer />
            <div className="flex h-full flex-1 flex-col items-center gap-4 overflow-x-auto rounded-xl p-4">
                <From
                    data={data}
                    setData={setData}
                    errors={errors}
                    handleSubmit={handleSubmit}
                    processing={processing}
                    initialImage={doctor?.user?.profile_image_url}
                    setShowConfirm={setShowConfirm}
                    is_delete={true}
                    submitTitle="Update"
                    specialties={specialties}
                />
            </div>
            <ConfirmDialog
                title="Delete Profile"
                message="Are you sure you want to delete Your Profile?"
                onConfirm={handleConfirm}
                onCancel={handleCancel}
                isOpen={showConfirm}
            />
        </AppLayout>
    );
}
