import { Select } from 'antd';
import { useState } from 'react';
import PrescriptionChart from './PrescriptionChart'; // your LineChart component

const { Option } = Select;

export default function DoctorFilterChart({ data, doctorsMap }) {

    const doctorKeys = Object.keys(doctorsMap);

    // Default: show first 3 doctors
    const [selectedDoctors, setSelectedDoctors] = useState(doctorKeys.slice(0, 3));

    return (
        <div>
            <div className="mb-4">
                <Select
                    mode="multiple"
                    allowClear
                    placeholder="Select doctors"
                    value={selectedDoctors}
                    onChange={(val) => setSelectedDoctors(val)}
                    style={{ width: '100%' }}
                >
                    {doctorKeys.map((key) => (
                        <Option key={key} value={key}>
                            {doctorsMap[key]}
                        </Option>
                    ))}
                </Select>
            </div>

            <PrescriptionChart data={data} doctorsMap={Object.fromEntries(selectedDoctors.map((k) => [k, doctorsMap[k]]))} />
        </div>
    );
}
