import { Card, Select } from "antd";
import { useEffect, useState } from "react";

const { Option } = Select;

const PatientSearchSelect = ({ patients, data, setData }) => {
    const [patientId, setPatientId] = useState(undefined);

    useEffect(() => {
        if (data?.patient_id) {
            setPatientId(String(data.patient_id)); // always string
        }
    }, [data?.patient_id, patients]);

    const selectedPatient = patients.find(
        (p) => String(p.id) === String(patientId),
    );

    return (
        <div>
            <label className="text-lg font-bold">Select Patient</label>
            <Select
                allowClear
                showSearch
                style={{ width: "100%" }}
                placeholder="Search by name or mobile"
                value={patientId}
                onChange={(value) => {
                    setPatientId(value);
                    setData("patient_id", value);
                }}
                filterOption={(input, option) => {
                    const name = option?.name?.toLowerCase() || "";
                    const mobile = option?.mobile?.toLowerCase() || "";
                    return (
                        name.includes(input.toLowerCase()) ||
                        mobile.includes(input.toLowerCase())
                    );
                }}
            >
                {patients.map((p) => (
                    <Option
                        key={String(p.id)}
                        value={String(p.id)}
                        name={p.name}
                        mobile={p.mobile}
                    >
                        {p.name} ({p.mobile})
                    </Option>
                ))}
            </Select>

            {selectedPatient && (
                <Card
                    className="mt-4 border border-[var(--base-color)]"
                    title={selectedPatient.name}
                    extra={
                        <span className="-mr-3 rounded-l-md bg-[var(--base-color)] p-2 text-lg text-white capitalize shadow">
                            {selectedPatient.relationship}
                        </span>
                    }
                    size="small"
                >
                    <p>
                        <strong>ID:</strong> {selectedPatient.patient_number}
                    </p>
                    <p>
                        <strong>Mobile:</strong> {selectedPatient.mobile}
                    </p>
                    <p>
                        <strong>Email:</strong> {selectedPatient.email}
                    </p>
                    <p>
                        <strong>Gender:</strong> {selectedPatient.gender}
                    </p>
                    <p>
                        <strong>Blood Group:</strong>{" "}
                        {selectedPatient.blood_group}
                    </p>
                </Card>
            )}
        </div>
    );
};

export default PatientSearchSelect;
