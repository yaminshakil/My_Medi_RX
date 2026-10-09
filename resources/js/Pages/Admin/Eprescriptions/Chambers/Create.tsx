import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm, usePage } from '@inertiajs/react';
import { FormEventHandler } from 'react';
import Form from './Form';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Create Cambers',
        href: '/chambers/create',
    },
];

export default function Create() {
    const { hospitaloptions } = usePage().props;
    const { data, setData, post, processing, errors } = useForm({
        name: '',
        chamber_logo: '',
        footer_info: '',
        header_left: '',
        header_right: '',
        schedules: [
            { day: '', start_time: '', end_time: '', slot_duration: '' }, // at least one row
        ],
        fee: '',
        followup_fee: '',
        report_fee: '',
        city: '',
        address: '',
        appoinment_limit: '',
        hospital_id: hospitaloptions.length > 0 ? hospitaloptions[0].value : '', // default to first hospital if available
    });

    const handleSubmit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('chambers.store'));
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Add Chamber" />
            <div className="flex h-full flex-1 flex-col items-center gap-4 overflow-x-auto rounded-xl p-4">
                <Form
                    data={data}
                    setData={setData}
                    errors={errors}
                    handleSubmit={handleSubmit}
                    processing={processing}
                    heading="Add Chamber"
                    submitTitle="Create"
                    initialImage={null}
                    hospitals={hospitaloptions}
                />
            </div>
        </AppLayout>
    );
}
