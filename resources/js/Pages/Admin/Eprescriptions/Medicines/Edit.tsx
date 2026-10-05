import AppLayout from '@/Layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm, usePage } from '@inertiajs/react';
import Form from './Form';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Edit Medicine',
        href: '/medicines',
    },
];

export default function Edit() {
    const { medicine, manufacturers } = usePage().props;
    const { data, setData, put, processing, errors } = useForm({
        doctor_id: medicine.doctor_id,
        brand_name: medicine.brand_name,
        generic_name: medicine.generic_name,
        strength: medicine.strength,
        type: medicine.type,
        manufacturer_id: medicine.manufacturer_id,
        is_active: medicine.is_active,
    });

    function handleSubmit(e) {
        e.preventDefault();
        put(route('medicines.update', medicine.id));
    }

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Add Medicine" />
            <div className="flex h-full flex-1 flex-col items-center gap-4 overflow-x-auto rounded-xl p-4">
                <Form
                    data={data}
                    setData={setData}
                    handleSubmit={handleSubmit}
                    errors={errors}
                    processing={processing}
                    manufacturers={manufacturers}
                    heading="Edit Medicine"
                    submitTitle="Update"
                />
            </div>
        </AppLayout>
    );
}
