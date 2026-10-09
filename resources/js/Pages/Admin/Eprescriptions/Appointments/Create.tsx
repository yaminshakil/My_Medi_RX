import AppointmentCalendar from '@/components/Appointments/AppointmentCalendar';
import AvailableTimeSlot from '@/components/Appointments/AvailableTimeSlot';
import ConfirmBtn from '@/components/Appointments/ConfirmBtn';
import PatientSearchSelect from '@/components/Appointments/PatientSearchSelect';
import SelectChamber from '@/components/Appointments/SelectChamber';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/react';
import { useState } from 'react';

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Book Appointment', href: '/appointments' }];

export default function Create(props) {
    const [selectedChamber, setSelectedChamber] = useState(null);

    const { data, setData, post, errors, processing } = useForm({
        doctor_id: props.doctor.id,
        patient_id: props.patient_id,
        chamber_id: null,
        appointment_date: '',
        appointment_time: '',
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        post(route('appointments.store'), {
            onSuccess: () => console.log('Appointment booked!'),
        });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Book Appointment" />
            <div className="mx-auto max-w-6xl p-4 md:w-2/3">
                <h2 className="mb-6 text-2xl font-bold">Book Appointment with {props.doctor.name}</h2>

                {/* Chamber Selection */}
                <SelectChamber
                    data={data}
                    setData={setData}
                    chambers={props.doctor.chambers}
                    selectedChamber={selectedChamber}
                    setSelectedChamber={setSelectedChamber}
                />

                {/* Calendar */}
                {selectedChamber && (
                    <div className="mb-6 grid grid-cols-1 items-start gap-6 rounded-xl bg-white p-4 shadow md:grid-cols-2">
                        <AppointmentCalendar data={data} setData={setData} selectedChamber={selectedChamber} />

                        <div className="w-full">
                            {props.patient_id == null && (
                                <PatientSearchSelect patients={props.patients} data={data} setData={setData} errors={errors} />
                            )}
                        </div>
                    </div>
                )}

                {/* Time Slots */}
                {data.appointment_date && selectedChamber && (
                    <AvailableTimeSlot data={data} setData={setData} selectedChamber={selectedChamber} appointments={props.appointments} />
                )}

                {/* Submit */}
                {data.appointment_time && <ConfirmBtn handleSubmit={handleSubmit} processing={processing} />}
            </div>
        </AppLayout>
    );
}
