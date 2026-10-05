import AddPatient from '@/Components/AddPatient';
import AddVital from '@/Components/AddVital';
import { CommonModal } from '@/Components/CommonModal';
import { ConfirmDialog } from '@/Components/ConfirmDialog';
import GynaeHistoryForm from '@/Components/GynaeHistoryForm';
import GynaeHistorySidebar from '@/Components/GynaeHistorySidebar';
import InputError from '@/Components/input-error';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Textarea } from '@/Components/ui/textarea';
import AppLayout from '@/Layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm, usePage } from '@inertiajs/react';
import axios from 'axios';
import { History, LoaderCircle, PlusIcon, X } from 'lucide-react';
import { FormEventHandler, useEffect, useState } from 'react';
import DateTimePicker from 'react-datetime-picker';
import { ToastContainer, toast } from 'react-toastify';
import AddMedicine from './AddMedicine';
import Footer from './Footer';
import Header from './Header';
import InstructionSelector from './InstructionSelector';
import MedicationsList from './MedicationsList';
import PatientInfo from './PatientInfo';

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Create Prescription', href: '/prescriptions' }];

export default function Create(props) {
    const { flash } = usePage().props;
    const [showAddVitalModal, setShowAddVitalModal] = useState(false);
    const [showAddPatientModal, setShowAddPatientModal] = useState(false);
    const [medicines, setMedicines] = useState([]);
    const [patients, setPatients] = useState([]);
    const [showConfirm, setShowConfirm] = useState(false);
    const [addMedicineShowConfirm, setAddMedicineShowConfirm] = useState(false);
    const [localErrors, setLocalErrors] = useState({});
    const [selectedInstructions, setSelectedInstructions] = useState([]);
    const [editingIndex, setEditingIndex] = useState(null);
    const [editingMedication, setEditingMedication] = useState({
        type: '',
        medicine_id: null,
        brand_name: '',
        strength: '',
        dosage: '',
        duration: '',
        advice: '',
        meal_time: '',
    });

    const [showAddGynaeHiModal, setShowAddGynaeHiModal] = useState(false);
    const [loadingButton, setLoadingButton] = useState(null); // 'saveNew' | 'saveComplete' | null

    const { data, setData, post, errors } = useForm({
        patient_id: '',
        followup_date: '',
        diagnosis: '',
        symptoms: [{ name: '' }],
        medications: [],
        vital_id: '',
        instructions: '',
        follow_up_advice: '',
        onexaminations: [],
        investigations: [],
        status: 'issued',
        appointment_id: '',
        gynae_history: {
            marital_status: '',
            marriage_duration: '',
            consanguinity: '',
            menarche_age: '',
            lmp: '',
            cycle: '',
            flow: '',
            dysmenorrhea: false,
            contraceptive_use: false,
            gravida: '',
            para: '',
            abortion: '',
            living_children: '',
            edd: '',
            anc: '',
            other_history: '',
        },
    });

    const {
        data: vitalData,
        setData: setVitalData,
        post: postVital,
    } = useForm({
        patient_id: '',
        blood_pressure: '',
        heart_rate: '',
        temperature: '',
        respiratory_rate: '',
        oxygen_saturation: '',
        weight: '',
        height: '',
        bmi: '',
        notes: '',
    });

    const onHandleVitalChange = (event) => {
        setVitalData(event.target.name, event.target.type === 'checkbox' ? event.target.checked : event.target.value);
    };

    const handleAddvital = () => {
        postVital(route('vitals.store'), {
            preserveScroll: true,
            onSuccess: () => {
                fetchPatients();
                setShowAddVitalModal(false);
            },
            onError: (errors) => {
                console.log(errors);
            },
        });
    };

    const handleAddvitalCancel = () => {
        setShowAddVitalModal(false);
    };

    /* Add patient */
    const {
        data: patientData,
        setData: setPatientData,
        post: postPatient,
        errors: errorsPateint,
    } = useForm({
        name: '',
        phone: '',
        email: '',
        date_of_birth: '',
        address: '',
        city: '',
        gender: '',
        blood_group: '',
        marital_status: '',
    });

    const onHandlePatientChange = (event) => {
        setPatientData(event.target.name, event.target.type === 'checkbox' ? event.target.checked : event.target.value);
    };

    const handleAddPatient = () => {
        postPatient(route('prescriptions.createpatient'), {
            onSuccess: () => {
                setShowAddPatientModal(false);
                fetchPatients();
            },
        });
    };

    const handleAddPateintCancel = () => {
        setShowAddPatientModal(false);
    };

    const selectedPatient = patients.find((p) => p.id === data.patient_id);

    useEffect(() => {
        if (selectedPatient?.appointment) {
            setData((prev) => ({ ...prev, appointment_id: selectedPatient?.appointment.id, vital_id: selectedPatient?.vital?.id }));
        }
    }, [selectedPatient]);

    const fetchPatients = () => {
        axios
            .get('/admin/eprescriptions/get-patients')
            .then((res) => setPatients(res.data))
            .catch(console.error);
    };

    useEffect(() => {
        fetchPatients();
    }, []);

    // Prescription

    function Content({ content }) {
        return <div className="ql-editor" dangerouslySetInnerHTML={{ __html: content }} />;
    }

    const addComplaint = () => {
        setData('symptoms', [...data.symptoms, { name: '' }]);
    };

    const updateComplaient = (index, field, value) => {
        const symptoms = [...data.symptoms];
        symptoms[index][field] = value;
        setData('symptoms', symptoms);
        if (value && value.trim() !== '') {
            setLocalErrors((prev) => ({ ...prev, symptoms: null }));
        }
    };

    const handleSymptomRemove = (index) => {
        const list = [...data.symptoms];
        list.splice(index, 1);
        setData('symptoms', list);
    };

    const handleMedicineRemove = (index) => {
        const list = [...data.medications];
        list.splice(index, 1);
        setData('medications', list);
    };

    const onSubmit: FormEventHandler = (e) => {
        e.preventDefault();
        setLoadingButton('saveComplete');
        if (!data.patient_id || !data.medications.length || !data.appointment_id) {
            setShowConfirm(true);
            return true;
        }

        // Check if symptoms are empty or have blank values
        const hasComplaint = Array.isArray(data.symptoms) && data.symptoms.some((s) => s.name && s.name.trim() !== '');

        if (!hasComplaint) {
            setLocalErrors((prev) => ({ ...prev, symptoms: 'Chief Complaint is required.' }));
            setLoadingButton(null);
            return;
        }

        setLocalErrors((prev) => ({ ...prev, symptoms: null }));
        post(route('prescriptions.store'), {
            onSuccess: () => setLoadingButton(null),
            onError: () => setLoadingButton(null),
            onFinish: () => setLoadingButton(null),
        });
    };

    // Load medicines
    useEffect(() => {
        axios.get('/admin/get-medicines').then((res) => {
            const options = res.data.map((m) => ({ value: m.id, label: m.brand_name + ' ' + m.strength, strength: m.strength, type: m.type }));
            setMedicines(options);
        });
    }, []);

    // On Examinations

    const onExamination = () => {
        setData('onexaminations', [...data.onexaminations, { name: '' }]);
    };

    const updateOnExamination = (index, field, value) => {
        const onexaminations = [...data.onexaminations];
        onexaminations[index][field] = value;
        setData('onexaminations', onexaminations);
    };

    const handleOnExamination = (index) => {
        const list = [...data.onexaminations];
        list.splice(index, 1);
        setData('onexaminations', list);
    };

    // On Investigations

    const addInvestigation = () => {
        setData('investigations', [...data.investigations, { name: '' }]);
    };

    const updateInvestigation = (index, field, value) => {
        const investigations = [...data.investigations];
        investigations[index][field] = value;
        setData('investigations', investigations);
    };

    const handleInvestigation = (index) => {
        const list = [...data.investigations];
        list.splice(index, 1);
        setData('investigations', list);
    };

    const onChangeDate = (date) => {
        if (date) {
            // Create UTC date at midnight
            const utcMidnight = new Date(Date.UTC(date.getFullYear(), date.getMonth(), date.getDate()));

            setData('followup_date', utcMidnight);
        }
    };

    const quickAppointment = (e) => {
        e.preventDefault();
        post(route('prescriptions.saveandnew.quickAppointment', data.patient_id), {
            onSuccess: () => {
                fetchPatients();
            },
        });
    };

    const saveAndNew: FormEventHandler = (e) => {
        e.preventDefault();
        setLoadingButton('saveNew');
        if (!data.patient_id || !data.medications.length || !data.appointment_id) {
            setShowConfirm(true);
            return true;
        }
        // Check if symptoms are empty or have blank values
        const hasComplaint = Array.isArray(data.symptoms) && data.symptoms.some((s) => s.name && s.name.trim() !== '');

        if (!hasComplaint) {
            setLocalErrors((prev) => ({ ...prev, symptoms: 'Chief Complaint is required.' }));
            setLoadingButton(null);
            return;
        }

        setLocalErrors((prev) => ({ ...prev, symptoms: null }));

        post(route('prescriptions.saveandnew.store'), {
            onSuccess: (res) => {
                window.open(route('eprescription.pdf', res.props.flash.message.uuid), '_blank');
                fetchPatients();
                setData((prev) => ({
                    ...prev,
                    patient_id: '',
                    followup_date: '',
                    diagnosis: '',
                    symptoms: [{ name: '' }],
                    medications: [],
                    vital_id: '',
                    instructions: '',
                    follow_up_advice: '',
                    onexaminations: [],
                    investigations: [],
                    status: 'issued',
                    appointment_id: '',
                    gynae_history: {
                        marital_status: '',
                        marriage_duration: '',
                        consanguinity: '',
                        menarche_age: '',
                        lmp: '',
                        cycle: '',
                        flow: '',
                        dysmenorrhea: false,
                        contraceptive_use: false,
                        gravida: '',
                        para: '',
                        abortion: '',
                        living_children: '',
                        other_history: '',
                    },
                }));
                setSelectedInstructions([]);
                setEditingIndex(null);
                setLoadingButton(null);
            },
            onError: () => setLoadingButton(null),
            onFinish: () => setLoadingButton(null),
        });
    };

    // if Laravel flashed an ID, open PDF
    useEffect(() => {
        if (flash.message.uuid && flash.message.is_followup) {
            window.open(route('eprescription.pdf', flash.message.uuid), '_blank');
            return () => (flash.message.uuid = null);
        }
    }, [flash.message.uuid]);

    const handleConfirm = () => {
        setShowConfirm(false);
    };

    const handleCancel = () => {
        setShowConfirm(false);
    };

    const saveMedication = () => {
        if (!editingMedication.medicine_id) {
            setAddMedicineShowConfirm(true);
            return;
        }

        const meds = [...data.medications];

        if (editingIndex !== null) {
            // Editing existing medication
            meds[editingIndex] = { ...editingMedication };
        } else {
            // Adding new medication
            meds.push({ ...editingMedication });
        }

        setData('medications', meds);

        // Reset form
        setEditingIndex(null);
        setEditingMedication({
            type: '',
            medicine_id: null,
            brand_name: '',
            strength: '',
            dosage: '',
            duration: '',
            advice: '',
            meal_time: '',
        });
    };

    const handleGynaeHistory = () => {
        setShowAddGynaeHiModal(false);
    };

    const handleGynaeHistoryCancel = () => {
        setShowAddGynaeHiModal(false);
    };

    useEffect(() => {
        const patient = patients.find((p) => p.id === data.patient_id);
        if (patient?.appointment) {
            setData((prev) => ({
                ...prev,
                appointment_id: patient.appointment.id,
                vital_id: patient.vital?.id,
            }));
        }
    }, [patients, data.patient_id]);

    const mimealtimeOption = [
        { label: 'Before Meal', value: 'Before Meal' },
        { label: 'After Meal', value: 'After Meal' },
    ];

    useEffect(() => {
        if (flash.message.success) {
            toast.success(flash.message.success);
        }
        if (flash.message.error) {
            toast.error(flash.message.error);
        }
    }, [flash]);

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="E-Prescription" />
            <ToastContainer />
            <div className="container mx-auto">
                <Header
                    patients={patients}
                    data={data}
                    setData={setData}
                    selectedPatient={selectedPatient}
                    setShowAddPatientModal={setShowAddPatientModal}
                    quickAppointment={quickAppointment}
                />
                <header className="flex items-center justify-between border-b bg-white px-6 py-3 shadow">
                    {/* Left - Chamber Address */}
                    <div className="flex items-center space-x-2 text-sm">
                        <div className="text-left">
                            <div className="border-b p-2">
                                <div className="text-md font-medium">
                                    <Content content={props?.doctor?.chamber?.header_left} />
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Center - Logo */}
                    {props?.doctor?.chamber?.chamber_logo && (
                        <div className="flex items-center justify-center">
                            <img src={'/storage/' + props?.doctor?.chamber?.chamber_logo} alt="Clinic Logo" className="h-24" />
                        </div>
                    )}

                    {/* Right - Doctor Profile */}
                    <div className="flex flex-col items-center space-x-3">
                        <div className="text-right">
                            <div className="text-md font-medium">
                                <Content content={props?.doctor?.chamber?.header_right} />
                            </div>
                        </div>
                    </div>
                </header>

                {/* Body */}
                <div className="flex flex-1 flex-col overflow-hidden md:flex-row">
                    {/* Sidebar */}
                    <aside className="flex w-full flex-col bg-white shadow-lg md:w-72">
                        <div className="mb-2 ml-2 flex items-center space-x-2">
                            <History className="h-5 w-5 text-indigo-500" />
                            <span className="font-semibold">Patient History</span>
                        </div>

                        {/* Patient History */}
                        <div className="flex-1 overflow-y-auto p-4">
                            <div className="space-y-2 rounded-lg border bg-gray-50 p-3 text-sm">
                                <>
                                    <div>
                                        <Button
                                            type="button"
                                            className="mb-2 w-full cursor-pointer justify-center text-white"
                                            onClick={() => addComplaint()}
                                        >
                                            <PlusIcon className="cursor-pointer" size={14} />
                                            Chief Complaint(cc)
                                        </Button>
                                        {data.symptoms.map((symptom, index) => (
                                            <div key={index} className="mt-2 flex">
                                                <Input
                                                    placeholder="Complaint"
                                                    value={symptom.name}
                                                    onChange={(e) => updateComplaient(index, 'name', e.target.value)}
                                                />
                                                {data.symptoms.length !== 1 && (
                                                    <X
                                                        className="mt-3 cursor-pointer text-red-700"
                                                        onClick={() => handleSymptomRemove(index)}
                                                        size={15}
                                                    />
                                                )}
                                            </div>
                                        ))}
                                        {/* Show validation error */}
                                        {(localErrors.symptoms || errors.symptoms) && (
                                            <p className="mt-1 text-sm text-red-600">{localErrors.symptoms || errors.symptoms}</p>
                                        )}
                                    </div>

                                    <div>
                                        {selectedPatient?.vital ? (
                                            <ul className="ml-5 list-disc">
                                                <h2 className="mb-2 font-bold">Vitals</h2>
                                                <div key={selectedPatient.vital.id}>
                                                    {selectedPatient.vital.blood_pressure && (
                                                        <li>BP: {selectedPatient.vital.blood_pressure || ' '}</li>
                                                    )}
                                                    {selectedPatient.vital.heart_rate && <li>HR: {selectedPatient.vital.heart_rate || ' '}</li>}
                                                    {selectedPatient.vital.temperature && <li>Temp: {selectedPatient.vital.temperature || ' '}</li>}
                                                    {selectedPatient.vital.oxygen_saturation && (
                                                        <li>SpO₂: {selectedPatient.vital.oxygen_saturation || ' '}</li>
                                                    )}
                                                    {selectedPatient.vital.respiratory_rate && (
                                                        <li>RR: {selectedPatient.vital.respiratory_rate || ' '}</li>
                                                    )}
                                                    {selectedPatient.vital.weight && <li>Wt: {selectedPatient.vital.weight || ' '}</li>}
                                                    {selectedPatient.vital.height && <li>Ht: {selectedPatient.vital.height || ' '}</li>}
                                                    {selectedPatient.vital.bmi && <li>BMI: {selectedPatient.vital.bmi || ' '}</li>}
                                                    {selectedPatient.vital.notes && <li>Notes: {selectedPatient.vital.notes || ' '}</li>}
                                                </div>
                                            </ul>
                                        ) : (
                                            <div>
                                                <Button
                                                    type="button"
                                                    className="mb-2 w-full cursor-pointer justify-center text-white"
                                                    onClick={() => {
                                                        setShowAddVitalModal(true);
                                                        setVitalData('patient_id', data.patient_id);
                                                    }}
                                                >
                                                    <PlusIcon className="cursor-pointer" size={14} /> Add Vital
                                                </Button>
                                            </div>
                                        )}
                                    </div>

                                    {/* On Examination */}
                                    <div>
                                        <Button
                                            type="button"
                                            className="mb-2 w-full cursor-pointer justify-center text-white"
                                            onClick={() => onExamination()}
                                        >
                                            <PlusIcon className="cursor-pointer" size={14} />
                                            On Examinations (O/Ex)
                                        </Button>
                                        {data.onexaminations.map((onexamination, index) => (
                                            <div key={index} className="mt-2 flex">
                                                <Input
                                                    placeholder="On Examination"
                                                    value={onexamination.name}
                                                    onChange={(e) => updateOnExamination(index, 'name', e.target.value)}
                                                />
                                                {data.onexaminations.length !== 0 && (
                                                    <X
                                                        className="mt-3 cursor-pointer text-red-700"
                                                        onClick={() => handleOnExamination(index)}
                                                        size={15}
                                                    />
                                                )}
                                            </div>
                                        ))}
                                    </div>

                                    {/* On Investigations */}
                                    <div>
                                        <Button
                                            type="button"
                                            className="mb-2 w-full cursor-pointer justify-center text-white"
                                            onClick={() => addInvestigation()}
                                        >
                                            <PlusIcon className="cursor-pointer" size={14} />
                                            Investigations
                                        </Button>
                                        {data.investigations.map((investigation, index) => (
                                            <div key={index} className="mt-2 flex">
                                                <Input
                                                    placeholder="Investigation"
                                                    value={investigation.name}
                                                    onChange={(e) => updateInvestigation(index, 'name', e.target.value)}
                                                />
                                                {data.investigations.length !== 0 && (
                                                    <X
                                                        className="mt-3 cursor-pointer text-red-700"
                                                        onClick={() => handleInvestigation(index)}
                                                        size={15}
                                                    />
                                                )}
                                            </div>
                                        ))}
                                    </div>

                                    {/* Gyne. & Obs. History */}
                                    <div>
                                        <Button
                                            type="button"
                                            className="mb-2 w-full cursor-pointer justify-center text-white"
                                            onClick={() => {
                                                setShowAddGynaeHiModal(true);
                                            }}
                                        >
                                            <PlusIcon className="cursor-pointer" size={14} /> Gyne. & Obs. History
                                        </Button>
                                        {data.gynae_history && (
                                            <GynaeHistorySidebar
                                                gynaeHistory={data.gynae_history}
                                                setGynaeHistory={(newHistory) => setData('gynae_history', newHistory)}
                                            />
                                        )}
                                    </div>

                                    {/* Diagnosis */}
                                    <div>
                                        <label className="mb-1 block text-sm font-medium">Diagnosis</label>
                                        <Textarea
                                            className="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            rows={3}
                                            value={data.diagnosis}
                                            name="diagnosis"
                                            onChange={(e) => setData('diagnosis', e.target.value)}
                                            placeholder="Enter diagnosis here..."
                                        />
                                    </div>
                                </>
                            </div>
                        </div>
                    </aside>

                    {/* Main Content */}
                    <main className="flex-1 overflow-y-auto p-6">
                        <div className="space-y-4 rounded-lg bg-white p-6 shadow">
                            {/* Patient Select */}
                            <PatientInfo selectedPatient={selectedPatient} />

                            {/* Medicines */}
                            <div>
                                <Label className="mb-1 block text-sm font-medium">Medicines</Label>

                                <AddMedicine
                                    editingMedication={editingMedication}
                                    setEditingMedication={setEditingMedication}
                                    medicines={medicines}
                                    medicine_doses={props?.doctor?.medicine_doses}
                                    medicine_durations={props?.doctor?.medicine_durations}
                                    mimealtimeOption={mimealtimeOption}
                                    editingIndex={editingIndex}
                                    saveMedication={saveMedication}
                                />

                                {/* Medications List */}

                                <MedicationsList
                                    medications={data.medications}
                                    onReorder={(newList) => setData('medications', newList)}
                                    setEditingIndex={setEditingIndex}
                                    setEditingMedication={setEditingMedication}
                                    handleMedicineRemove={handleMedicineRemove}
                                />
                            </div>

                            {/* Notes */}
                            <InstructionSelector
                                data={data}
                                setData={setData}
                                selectedInstructions={selectedInstructions}
                                setSelectedInstructions={setSelectedInstructions}
                            />

                            {/* Followup Advice */}
                            <div>
                                <Label className="mb-1 block text-sm font-medium">Followup Advice</Label>
                                <Textarea
                                    className="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    rows={2}
                                    value={data.follow_up_advice}
                                    onChange={(e) => setData('follow_up_advice', e.target.value)}
                                    placeholder="Followup Advice..."
                                />
                            </div>

                            <div>
                                <Label htmlFor="followup_date">Followup Date</Label>
                                <DateTimePicker
                                    required={true}
                                    onChange={(e) => {
                                        onChangeDate(e);
                                    }}
                                    value={data.followup_date}
                                    name="followup_date"
                                    format="y-MM-dd"
                                    className="w-full bg-white sm:w-auto"
                                />

                                {errors.followup_date && <InputError className="mt-2 text-red-700" message={errors.followup_date} />}
                            </div>

                            {/* Actions */}
                            <div className="flex justify-end space-x-3">
                                <Button
                                    onClick={(e) => saveAndNew(e)}
                                    type="button"
                                    data-testid="save-and-new"
                                    className="text-white"
                                    disabled={loadingButton !== null}
                                >
                                    {loadingButton === 'saveNew' && <LoaderCircle className="h-4 w-4 animate-spin" />}
                                    Save and New
                                </Button>
                                <Button onClick={(e) => onSubmit(e)} type="button" className="text-white" disabled={loadingButton !== null}>
                                    {loadingButton === 'saveComplete' && <LoaderCircle className="h-4 w-4 animate-spin" />}
                                    Save and Complete
                                </Button>
                            </div>
                        </div>
                    </main>
                </div>
                <Footer content={props?.doctor?.chamber?.footer_info} />
                <CommonModal
                    onConfirm={handleAddvital}
                    onCancel={handleAddvitalCancel}
                    isOpen={showAddVitalModal}
                    onClose={() => setShowAddVitalModal(false)}
                    title="Add Vital"
                >
                    <AddVital vitalData={vitalData} setVitalData={setVitalData} onHandleChange={onHandleVitalChange} />
                </CommonModal>
                {/* Add Patient */}
                <CommonModal
                    onConfirm={handleAddPatient}
                    onCancel={handleAddPateintCancel}
                    isOpen={showAddPatientModal}
                    onClose={() => setShowAddPatientModal(false)}
                    title="Add Patient"
                >
                    <AddPatient
                        patientData={patientData}
                        setPatientData={setPatientData}
                        onHandleChange={onHandlePatientChange}
                        errorsPateint={errorsPateint}
                    />
                </CommonModal>
                <ConfirmDialog
                    title="Some Thing Messing"
                    message={
                        !data.patient_id
                            ? 'You have net selected any Patient?'
                            : !data.appointment_id
                                ? 'This Patient have not any Appointment?'
                                : !data.medications[0]?.medicine_id
                                    ? 'You did not added any medicine!'
                                    : 'You have net selected any Patient?'
                    }
                    onConfirm={handleConfirm}
                    onCancel={handleCancel}
                    isOpen={showConfirm}
                />
                <ConfirmDialog
                    title="Select a Medicine"
                    message="Please select a medicine before adding."
                    onConfirm={() => setAddMedicineShowConfirm(false)}
                    onCancel={() => setAddMedicineShowConfirm(false)}
                    isOpen={addMedicineShowConfirm}
                />

                {/* Add Gynae History */}
                <CommonModal
                    onConfirm={handleGynaeHistory}
                    onCancel={handleGynaeHistoryCancel}
                    isOpen={showAddGynaeHiModal}
                    onClose={() => setShowAddGynaeHiModal(false)}
                    title="Gynae History"
                >
                    <GynaeHistoryForm data={data} setData={setData} errors={errors} />
                </CommonModal>
            </div>
        </AppLayout>
    );
}
