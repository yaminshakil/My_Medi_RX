import InputError from '@/components/input-error';
import { useAutoFocusError } from '@/lib/utils';
import { Card, Select } from 'antd';
import type { BaseSelectRef } from 'antd/es/select';
import { useRef } from 'react';

const { Option } = Select;

const PatientSearchSelect = ({ patients, data, setData, errors }) => {
    const selectedPatient = patients.find((p) => p.id === data.patient_id);
    const refs = {
        patient_id: useRef<BaseSelectRef | null>(null),
    };
    useAutoFocusError(errors, refs);

    return (
        <div>
            <label className="text-lg font-bold">Select Patient</label>
            <Select
                ref={(el) => {
                    refs.patient_id.current = el;
                }}
                allowClear
                showSearch
                style={{ width: '100%' }}
                placeholder="Search by name or mobile"
                value={data.patient_id}
                onChange={(values) => setData('patient_id', values)}
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
            <InputError message={errors.patient_id} className="mt-2" />
            {/* Show selected patient info */}
            {selectedPatient && (
                <Card className="mt-4" title={selectedPatient.name} size="small">
                    <p>
                        <strong>ID:</strong> {selectedPatient.patient_number}
                    </p>
                    <p>
                        <strong>Mobile:</strong> {selectedPatient.mobile}
                    </p>
                    <p>
                        <strong>Gender:</strong> {selectedPatient.gender}
                    </p>
                    <p>
                        <strong>Blood Group:</strong> {selectedPatient.blood_group}
                    </p>
                </Card>
            )}
        </div>
    );
};

export default PatientSearchSelect;
