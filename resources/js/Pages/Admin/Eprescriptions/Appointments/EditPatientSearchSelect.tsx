import { Card } from 'antd';

const PatientSearchSelect = ({ patient }) => {
    return (
        <div>
            <label className="text-lg font-bold">Patient Info</label>
            {/* Show selected patient info */}
            {patient && (
                <Card className="mt-4" title={patient.name} size="small">
                    <p>
                        <strong>ID:</strong> {patient.patient_number}
                    </p>
                    <p>
                        <strong>Mobile:</strong> {patient.mobile}
                    </p>
                    <p>
                        <strong>Gender:</strong> {patient.gender}
                    </p>
                    <p>
                        <strong>Blood Group:</strong> {patient.blood_group}
                    </p>
                </Card>
            )}
        </div>
    );
};

export default PatientSearchSelect;
