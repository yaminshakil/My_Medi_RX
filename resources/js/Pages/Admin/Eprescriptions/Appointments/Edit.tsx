import AppointmentCalendar from '@/components/Appointments/AppointmentCalendar';
import AvailableTimeSlot from '@/components/Appointments/AvailableTimeSlot';
import EditPatientSearchSelect from '@/components/Appointments/EditPatientSearchSelect';
import SelectChamber from '@/components/Appointments/SelectChamber';
import UpdateBtn from '@/components/Appointments/UpdateBtn';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/react';
import dayjs from 'dayjs';
import { useEffect, useState } from 'react';

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Edit Appointment', href: '/appointments' }];

export default function Edit(props) {
    const [selectedChamber, setSelectedChamber] = useState(props.appointment.chamber);

    const { data, setData, put, processing } = useForm({
        doctor_id: props.appointment.doctor_id,
        patient_id: props.appointment.patient_id,
        chamber_id: props.appointment.chamber_id,
        appointment_date: props.appointment.appointment_date,
        appointment_time: props.appointment.appointment_time
            ? dayjs(props.appointment.appointmentdatetime, 'YYYY-MM-DD HH:mm').format('HH:mm:ss')
            : '',
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        put(route('appointments.update', props.appointment.id), {
            onSuccess: () => console.log('Appointment booked!'),
        });
    };

    useEffect(() => {
        if (props.appointment.appointment_date) {
            setData('appointment_date', dayjs(props.appointment.appointment_date).format('YYYY-MM-DD'));
        }
    }, [props.appointment.appointment_date]);

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Book Appointment" />
            <div className="mx-auto max-w-6xl p-6 md:w-2/3">
                <h2 className="mb-6 text-2xl font-bold">Update Appointment with {props.appointment.doctor.name}</h2>

                {/* Chamber Selection */}
                <SelectChamber
                    data={data}
                    setData={setData}
                    chambers={props.appointment.doctor.chambers}
                    selectedChamber={selectedChamber}
                    setSelectedChamber={setSelectedChamber}
                />

                {/* Calendar */}
                {selectedChamber && (
                    <div className="mb-6 grid grid-cols-1 items-start gap-6 rounded-xl bg-white p-4 shadow md:grid-cols-2">
                        <AppointmentCalendar data={data} setData={setData} selectedChamber={selectedChamber} />

                        <div className="w-full">
                            {props.patient_id == null && (
                                <EditPatientSearchSelect patient={props.appointment.patient} data={data} setData={setData} />
                            )}
                        </div>
                    </div>
                )}

                {/* Time Slots */}
                {data.appointment_date && selectedChamber && (
                    <AvailableTimeSlot data={data} setData={setData} selectedChamber={selectedChamber} appointments={props.appointments} />
                )}

                {/* Submit */}
                {data.appointment_time && <UpdateBtn handleSubmit={handleSubmit} processing={processing} />}
            </div>
        </AppLayout>
    );
}
