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
import { Head, router, useForm } from '@inertiajs/react';
import axios from 'axios';
import { History, LoaderCircle, PlusIcon, X } from 'lucide-react';
import { FormEventHandler, useEffect, useState } from 'react';
import DateTimePicker from 'react-datetime-picker';
import AddMedicine from './AddMedicine';
import Footer from './Footer';
import InstructionSelector from './InstructionSelector';
import MedicationsList from './MedicationsList';
import PatientInfo from './PatientInfo';

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Followup Prescription', href: '/prescriptions' }];

export default function EditPrescription(props) {
    const [showAddVitalModal, setShowAddVitalModal] = useState(false);
    const [medicines, setMedicines] = useState([]);
    const [showConfirm, setShowConfirm] = useState(false);
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
    const [localErrors, setLocalErrors] = useState({});

    const { data, setData, post, errors } = useForm({
        patient_id: '',
        followup_date: new Date(props.prescription.data.followup_date),
        diagnosis: props.prescription.data.diagnosis,
        symptoms: props.prescription.data.symptoms,
        medications: props.prescription.data.medications,
        vital_id: '',
        instructions: props.prescription.data.instructions,
        follow_up_advice: props.prescription.data.follow_up_advice,
        onexaminations: props.prescription.data.onexaminations,
        investigations: props.prescription.data.investigations,
        status: 'issued',
        appointment_id: props.appointment_id,
        is_followup: true,
        gynae_history: {
            marital_status: props.prescription?.data?.gynae_history?.marital_status || '',
            marriage_duration: props.prescription?.data?.gynae_history?.marriage_duration || '',
            consanguinity: props.prescription?.data?.gynae_history?.consanguinity || '',
            menarche_age: props.prescription?.data?.gynae_history?.menarche_age || '',
            lmp: props.prescription?.data?.gynae_history?.lmp || '',
            cycle: props.prescription?.data?.gynae_history?.cycle || '',
            flow: props.prescription?.data?.gynae_history?.flow || '',
            dysmenorrhea: props.prescription?.data?.gynae_history?.dysmenorrhea || false,
            contraceptive_use: props.prescription?.data?.gynae_history?.contraceptive_use || false,
            gravida: props.prescription?.data?.gynae_history?.gravida || '',
            para: props.prescription?.data?.gynae_history?.para || '',
            abortion: props.prescription?.data?.gynae_history?.abortion || '',
            living_children: props.prescription?.data?.gynae_history?.living_children || '',
            edd: props.prescription?.data?.gynae_history?.edd || '',
            anc: props.prescription?.data?.gynae_history?.anc || '',
            other_history: props.prescription?.data?.gynae_history?.other_history || '',
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

    const onHandleChange = (event) => {
        setVitalData(event.target.name, event.target.type === 'checkbox' ? event.target.checked : event.target.value);
    };

    const handleAddvital = () => {
        postVital(route('vitals.store'), {
            preserveScroll: true,
            onSuccess: () => {
                setShowAddVitalModal(false);
            },
        });
    };

    const handleAddvitalCancel = () => {
        setShowAddVitalModal(false);
    };

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
    useEffect(() => {
        if (props.prescription.data.patient) {
            setData((prev) => ({ ...prev, patient_id: props.prescription.data.patient.id }));
        }
        if (props.prescription.vitals) {
            setData((prev) => ({ ...prev, vital_id: props.prescription.vitals.id }));
        }
    }, [props.prescription.data.patient, props.prescription.data.doctor, props.prescription.vitals]);

    // Load medicines
    useEffect(() => {
        axios.get('/admin/get-medicines').then((res) => {
            const options = res.data.map((m) => ({ value: m.id, label: m.brand_name + ' ' + m.strength, strength: m.strength, type: m.type }));

            setMedicines(options);
            const medsWithNames = data.medications.map((med) => {
                const selected = options.find((o) => o.value === med.medicine_id);
                return {
                    ...med,
                    brand_name: selected?.label || med.brand_name || '',
                    strength: selected?.strength || med.strength || '',
                    type: selected?.type || med.type || '',
                };
            });
            setData((prev) => ({ ...prev, medications: medsWithNames }));
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

    const saveAndNew = (e) => {
        setLoadingButton('saveNew');
        if (!data.medications.length) {
            setShowConfirm(true);
            return true;
        }
        e.preventDefault();
        // Check if symptoms are empty or have blank values
        const hasComplaint = Array.isArray(data.symptoms) && data.symptoms.some((s) => s.name && s.name.trim() !== '');

        if (!hasComplaint) {
            setLocalErrors((prev) => ({ ...prev, symptoms: 'Chief Complaint is required.' }));
            setLoadingButton(null);
            return;
        }

        setLocalErrors((prev) => ({ ...prev, symptoms: null }));
        const payload = {
            ...data,
            followup_date: data.followup_date ? formatDateToYMD(new Date(data.followup_date)) : null,
        };
        router.post(route('prescriptions.saveandnew.store'), payload, {
            preserveScroll: true,
            onSuccess: () => setLoadingButton(null),
            onError: () => setLoadingButton(null),
            onFinish: () => setLoadingButton(null),
        });
    };

    const handleConfirm = () => {
        setShowConfirm(false);
    };

    const handleCancel = () => {
        setShowConfirm(false);
    };

    const handleChangeDate = (date) => {
        if (date) {
            // Create UTC date at midnight
            const utcMidnight = new Date(Date.UTC(date.getFullYear(), date.getMonth(), date.getDate()));

            setData('followup_date', utcMidnight);
        }
    };

    const saveMedication = () => {
        const meds = [...data.medications];
        if (editingMedication.medicine_id) {
            const selected = medicines.find((m) => m.value === editingMedication.medicine_id);
            editingMedication.brand_name = selected?.label || editingMedication.brand_name;
            editingMedication.strength = selected?.strength || editingMedication.strength;
            editingMedication.type = selected?.type || editingMedication.type;
        }

        if (editingIndex !== null) {
            meds[editingIndex] = { ...editingMedication };
        } else {
            meds.push({ ...editingMedication });
        }

        setData('medications', meds);

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

    // Initial Instruction load
    useEffect(() => {
        if (data.instructions) {
            // Split by newline and remove empty lines
            const selected = data.instructions.split('\n').filter(Boolean);
            setSelectedInstructions(selected);
        }
    }, [data.instructions]);

    const handleGynaeHistory = () => {
        setShowAddGynaeHiModal(false);
    };

    const handleGynaeHistoryCancel = () => {
        setShowAddGynaeHiModal(false);
    };

    const medicineDosesOptions = props.prescription?.data?.doctor?.medicine_doses.map((m) => ({ value: m.name, label: m.name }));
    const medicineDurationsOptions = props.prescription?.data?.doctor?.medicine_durations.map((m) => ({ value: m.name, label: m.name }));

    const formatDateToYMD = (date) => {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
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

    const mimealtimeOption = [
        { label: 'Before Meal', value: 'Before Meal' },
        { label: 'After Meal', value: 'After Meal' },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="E-Prescription" />
            <div className="container mx-auto">
                {/* Header */}
                <header className="flex items-center justify-between border-b bg-white px-6 py-3 shadow">
                    {/* Left - Chamber Address */}
                    <div className="flex items-center space-x-2 text-sm">
                        <div className="text-left">
                            <div className="border-b p-2">
                                <div className="text-md font-medium">
                                    <Content content={props.prescription.data?.doctor?.chamber.header_left} />
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Center - Logo */}
                    <div className="flex items-center justify-center">
                        <img src={'/storage/' + props.prescription.data?.doctor?.chamber?.chamber_logo} alt="Clinic Logo" className="h-24" />
                    </div>

                    {/* Right - Doctor Profile */}
                    <div className="flex flex-col items-center space-x-3">
                        <div className="text-right">
                            <div className="text-md font-medium">
                                <Content content={props.prescription.data?.doctor?.chamber?.header_right} />
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
                                                {index !== 0 && (
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
                                        <div>
                                            <Button
                                                type="button"
                                                className="mb-2 w-full cursor-pointer justify-center text-white"
                                                onClick={() => {
                                                    setShowAddVitalModal(true);
                                                    setVitalData('patient_id', props.prescription.data?.patient.id);
                                                }}
                                            >
                                                <PlusIcon className="cursor-pointer" size={14} /> Add Vital
                                            </Button>
                                            {props.prescription.vitals?.id && (
                                                <ul className="ml-5 list-disc">
                                                    <h2 className="mb-2 font-bold">Vitals</h2>

                                                    <div key={props.prescription.vitals?.id}>
                                                        {props.prescription.vitals?.blood_pressure && (
                                                            <li>BP: {props.prescription.vitals?.blood_pressure || ' '}</li>
                                                        )}
                                                        {props.prescription.vitals?.heart_rate && (
                                                            <li>HR: {props.prescription.vitals?.heart_rate || ' '}</li>
                                                        )}
                                                        {props.prescription.vitals?.temperature && (
                                                            <li>Temp: {props.prescription.vitals?.temperature || ' '}</li>
                                                        )}
                                                        {props.prescription.vitals?.oxygen_saturation && (
                                                            <li>SpO₂: {props.prescription.vitals?.oxygen_saturation || ' '}</li>
                                                        )}
                                                        {props.prescription.vitals?.respiratory_rate && (
                                                            <li>RR: {props.prescription.vitals?.respiratory_rate || ' '}</li>
                                                        )}
                                                        {props.prescription.vitals?.weight && <li>Wt: {props.prescription.vitals?.weight || ' '}</li>}
                                                        {props.prescription.vitals?.height && <li>Ht: {props.prescription.vitals?.height || ' '}</li>}
                                                        {props.prescription.vitals?.bmi && <li>BMI: {props.prescription.vitals?.bmi || ' '}</li>}
                                                        {props.prescription.vitals?.notes && (
                                                            <li>Notes: {props.prescription.vitals?.notes || ' '}</li>
                                                        )}
                                                    </div>
                                                </ul>
                                            )}
                                        </div>
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
                                            value={data.diagnosis}
                                            className="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            rows={3}
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
                            <PatientInfo selectedPatient={props.prescription.data?.patient} />

                            {/* Medicines */}
                            <div>
                                <Label className="mb-1 block text-sm font-medium">Medicines</Label>

                                <AddMedicine
                                    editingMedication={editingMedication}
                                    setEditingMedication={setEditingMedication}
                                    medicines={medicines}
                                    medicine_doses={medicineDosesOptions}
                                    medicine_durations={medicineDurationsOptions}
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
                                    value={data.follow_up_advice}
                                    className="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    rows={2}
                                    onChange={(e) => setData('follow_up_advice', e.target.value)}
                                    placeholder="Followup Advice..."
                                />
                            </div>

                            <div>
                                <Label htmlFor="followup_date">Followup Date</Label>
                                <DateTimePicker
                                    required={true}
                                    onChange={(e) => {
                                        handleChangeDate(e);
                                    }}
                                    value={data.followup_date}
                                    name="followup_date"
                                    format="y-MM-dd"
                                    className="w-full bg-white sm:w-auto"
                                />

                                <InputError className="mt-2 text-red-700" message={errors.followup_date} />
                            </div>

                            {/* Actions */}
                            <div className="flex justify-end space-x-3">
                                <Button onClick={(e) => saveAndNew(e)} type="button" className="" disabled={loadingButton !== null}>
                                    {loadingButton === 'saveNew' && <LoaderCircle className="h-4 w-4 animate-spin" />}
                                    Save and New
                                </Button>
                                <Button onClick={(e) => onSubmit(e)} type="button" className="" disabled={loadingButton !== null}>
                                    {loadingButton === 'saveComplete' && <LoaderCircle className="h-4 w-4 animate-spin" />}
                                    Save Prescription
                                </Button>
                            </div>
                        </div>
                    </main>
                </div>
                <Footer content={props.prescription.data?.doctor?.chamber?.footer_info} />
                <CommonModal
                    onConfirm={handleAddvital}
                    onCancel={handleAddvitalCancel}
                    isOpen={showAddVitalModal}
                    onClose={() => setShowAddVitalModal(false)}
                    title="Add Vital"
                >
                    <AddVital vitalData={vitalData} setVitalData={setVitalData} onHandleChange={onHandleChange} />
                </CommonModal>
                <ConfirmDialog
                    title="Validation Error!"
                    message={
                        !data.patient_id
                            ? 'You have net selected any Patient?'
                            : !data.appointment_id
                                ? 'This Patient have not any Appointment?'
                                : !data.medications?.length
                                    ? 'You did not added any medicine!'
                                    : 'You have net selected any Patient?'
                    }
                    onConfirm={handleConfirm}
                    onCancel={handleCancel}
                    isOpen={showConfirm}
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
