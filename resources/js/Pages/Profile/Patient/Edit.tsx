import { ConfirmDialog } from "@/Components/ConfirmDialog";
import AppLayout from "@/Layouts/app-layout";
import DonorProfileToggle from "@/Components/BloodBank/DonorProfileToggle";
import { type BreadcrumbItem, type PageProps } from "@/types";
import { Head, router, useForm, usePage } from "@inertiajs/react";
import { FormEventHandler, useEffect, useState } from "react";
import { ToastContainer, toast } from "react-toastify";
import From from "./Form";

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: "Patient Profile",
        href: "/pateint/profile",
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

type PatientProfilePageData = {
    patient: {
        id: number;
        name: string;
        phone: string;
        email: string | null;
        birth_date: string | null;
        address: string | null;
        city: string | null;
        gender: string | null;
        blood_group: string | null;
        marital_status: string | null;
        profile_image_url: string | null;
    };
    donor: {
        name: string;
        phone: string;
        blood_group: string;
        latitude: number | string;
        longitude: number | string;
        city: string | null;
        last_donated_at: string | null;
        is_verified: boolean;
        is_available: boolean;
    } | null;
};

export default function Edit() {
    const { patient, donor, flash } =
        usePage<PageProps<PatientProfilePageData>>().props;
    const [showConfirm, setShowConfirm] = useState(false);

    const { data, setData, post, errors, processing } = useForm<PatientForm>({
        name: patient.name || "",
        phone: patient.phone || "",
        email: patient.email || "",
        date_of_birth: patient.birth_date || "",
        address: patient.address || "",
        city: patient.city || "",
        gender: patient.gender || "",
        blood_group: patient.blood_group || "",
        profile_image: null,
        marital_status: patient.marital_status || "",
    });

    const handleSubmit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route("patients.profile.update", patient.id));
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
        router.delete(route("patients.profile.destroy", patient.id), {
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
            <Head title="Patient Profile" />
            <ToastContainer />
            <div className="flex h-full flex-1 flex-col items-center gap-4 overflow-x-auto rounded-xl p-4">
                <From
                    data={data}
                    setData={setData}
                    errors={errors}
                    handleSubmit={handleSubmit}
                    processing={processing}
                    initialImage={patient?.profile_image_url}
                    setShowConfirm={setShowConfirm}
                    is_delete={true}
                    submitTitle="Update"
                />
                <DonorProfileToggle patient={patient} donor={donor} />
            </div>
            <ConfirmDialog
                title="Delete Patient"
                message="Are you sure you want to delete Your Profile?"
                onConfirm={handleConfirm}
                onCancel={handleCancel}
                isOpen={showConfirm}
            />
        </AppLayout>
    );
}
