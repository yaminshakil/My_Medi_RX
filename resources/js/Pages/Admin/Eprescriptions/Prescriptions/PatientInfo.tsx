export default function ({ selectedPatient }) {
    return (
        <div>
            <ul className="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                {selectedPatient && (
                    <li key={selectedPatient.id} value={selectedPatient.id} className="flex flex-col items-center justify-between md:flex-row">
                        <span>
                            Patient: <strong>{selectedPatient.name}</strong>
                        </span>
                        <span>
                            Patient Id: <strong>{selectedPatient.patient_number}</strong>
                        </span>
                        <span>
                            Age:{' '}
                            <strong>
                                {selectedPatient.age.y} Yrs {selectedPatient.age.m} Mo
                            </strong>
                        </span>
                        <span>
                            Gender: <strong>{selectedPatient.gender}</strong>
                        </span>
                        <span>
                            Date: <strong>{selectedPatient.current_date}</strong>
                        </span>
                    </li>
                )}
            </ul>
        </div>
    );
}
