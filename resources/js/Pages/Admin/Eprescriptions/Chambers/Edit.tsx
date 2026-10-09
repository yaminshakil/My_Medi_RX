import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm, usePage } from '@inertiajs/react';
import { FormEventHandler } from 'react';
import Form from './Form';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Edit Cambers',
        href: '/chambers',
    },
];

export default function Edit(props) {
    const { hospitaloptions } = usePage().props;
    const { data, setData, post, processing, errors } = useForm({
        name: props?.doctorChamber?.name,
        header_left: props?.doctorChamber?.header_left || '',
        header_right: props?.doctorChamber?.header_right || '',
        footer_info: props?.doctorChamber?.footer_info || '',
        chamber_logo: null,
        prev_chamber_logo: props?.doctorChamber?.chamber_logo || '',
        schedules: props?.doctorChamber?.schedules?.length
            ? props?.doctorChamber?.schedules
            : [{ day: '', start_time: '', end_time: '', slot_duration: '' }],
        fee: props?.doctorChamber?.fee || '',
        followup_fee: props?.doctorChamber?.followup_fee || '',
        report_fee: props?.doctorChamber?.report_fee || '',
        city: props?.doctorChamber?.city || '',
        address: props?.doctorChamber?.address || '',
        appoinment_limit: props?.doctorChamber?.appoinment_limit || 0,
        hospital_id: props?.doctorChamber?.hospital_id || hospitals[0]?.value || '',
        _method: 'PUT',
    });

    const handleSubmit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('chambers.update', props.doctorChamber.id));
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Edit Chamber" />
            <div className="flex h-full flex-1 flex-col items-center gap-4 overflow-x-auto rounded-xl p-4">
                <Form
                    data={data}
                    setData={setData}
                    errors={errors}
                    handleSubmit={handleSubmit}
                    processing={processing}
                    heading="Edit Chamber"
                    submitTitle="Update"
                    initialImage={props?.doctorChamber?.chamber_logo_url}
                    hospitals={hospitals}
                />
            </div>
        </AppLayout>
    );
}
