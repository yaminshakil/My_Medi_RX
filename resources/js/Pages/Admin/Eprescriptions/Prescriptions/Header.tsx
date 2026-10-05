import { Button } from '@/Components/ui/button';
import { Link } from '@inertiajs/react';
import { Card, Select } from 'antd';
import dayjs from 'dayjs';
import { Repeat } from 'lucide-react';

const { Option } = Select;

export default function PatientAndAppointmentHeader({ patients, data, setData, selectedPatient, setShowAddPatientModal, quickAppointment }) {
    return (
        <header className="flex h-auto flex-col items-center justify-between border-b bg-white px-6 py-3 align-middle shadow md:h-40 md:flex-row">
            <div className="flex flex-col items-center space-x-2 text-sm md:flex-row">
                <div className="flex-1 text-left">
                    <label className="text-lg font-bold">Select Patient</label>
                    <Select
                        allowClear
                        showSearch
                        style={{ width: '100%' }}
                        placeholder="Search by name or mobile"
                        value={data.patient_id || undefined}
                        onChange={(value) => setData('patient_id', value)}
                        filterOption={(input, option) => {
                            const name = option?.name?.toLowerCase() || '';
                            const mobile = option?.mobile?.toLowerCase() || '';
                            return name.includes(input.toLowerCase()) || mobile.includes(input.toLowerCase());
                        }}
                    >
                        {patients.map((p) => (
                            <Option key={p.id} value={p.id} name={p.name} mobile={p.mobile}>
                                {p.name} ({p.mobile})
                            </Option>
                        ))}
                    </Select>
                </div>
                <Button onClick={() => setShowAddPatientModal(true)} type="button" className="mt-6 text-white">
                    + Add patient
                </Button>
            </div>
            {selectedPatient?.prescriptions.length > 0 && selectedPatient?.appointment?.id && (
                <div className="mt-4">
                    <Link
                        href={route('prescriptions.followup', {
                            id: selectedPatient?.prescriptions[0]?.id,
                            appointment_id: selectedPatient?.appointment?.id,
                        })}
                        className="flex gap-2 rounded-lg bg-[var(--btn-base-color)] px-4 py-2 text-white hover:bg-[var(--btn-base-hover-color)]"
                        target="_blank"
                    >
                        <Repeat /> Followup Patient
                    </Link>
                </div>
            )}
        </header>
    );
}
