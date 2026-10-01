<?php

namespace App\Http\Controllers\Admin\Eprescriptions;

use App\Http\Controllers\Controller;
use App\Http\Requests\Eprescription\CreateEprescriptionRequest;
use App\Http\Requests\Patients\CreatePatientRequest;
use App\Interfaces\Patients\PatientRepositoryInterface;
use App\Models\Prescription;
use App\Services\Eprescriptions\AppointmentService;
use App\Services\Eprescriptions\PrescriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class PrescriptionController extends Controller
{
    protected $prescriptionService;

    protected $appointmentService;

    protected $prescriptionpatient;

    protected $user;

    public function __construct(
        PrescriptionService $prescriptionService,
        AppointmentService $appointmentService,
        PatientRepositoryInterface $prescriptionpatient
    ) {
        $this->prescriptionService = $prescriptionService;
        $this->appointmentService = $appointmentService;
        $this->prescriptionpatient = $prescriptionpatient;
        $this->user = Auth::user();
    }

    public function index(Request $request)
    {
        $filters = $request->only(['search', 'perPage', 'sort_by', 'sort_direction']);
        $perPage = $request->input('perPage', 10);

        $prescriptions = $this->prescriptionService->all($filters, $perPage);

        if ($this->user->hasRole('Patient')) {
            return Inertia::render('Admin/Eprescriptions/Prescriptions/PatientIndex', [
                'prescriptions' => $prescriptions,
                'filters' => $request->only(['search', 'perPage', 'sort_by', 'sort_direction']),
            ]);
        } else {
            return Inertia::render('Admin/Eprescriptions/Prescriptions/Index', [
                'prescriptions' => $prescriptions,
                'filters' => $request->only(['search', 'perPage', 'sort_by', 'sort_direction']),
            ]);
        }
    }

    public function create()
    {
        $data = $this->prescriptionService->saveAndNewPrescription();

        return Inertia::render('Admin/Eprescriptions/Prescriptions/Create', $data);
    }

    public function store(CreateEprescriptionRequest $request)
    {
        $data = $request->validated();
        $prescription = $this->prescriptionService->store($data);

        return redirect()->route('patients.index')->with('prescription_id', $prescription->uuid);
    }

    public function edit($id)
    {
        return Inertia::render('Admin/Eprescriptions/Prescriptions/EditPrescription', [
            'prescription' => $this->prescriptionService->find($id),
        ]);
    }

    public function destroy($id)
    {
        $this->prescriptionService->delete($id);

        return redirect()->route('prescriptions.index')->with('success', 'Prescription deleted.');
    }

    public function downloadPdf($uuid)
    {
        // Load prescription with related patient, doctor, etc.
        $prescription = Prescription::with(['patient', 'doctor', 'vitals', 'medications', 'medications.medicines'])
            ->where('uuid', $uuid)->first();

        return view('eprescriptions.pdf', compact('prescription'));
    }

    public function getAllMedicine()
    {
        $data = $this->prescriptionService->getAllMedicine();

        return $data;
    }

    public function followup($id, $appointment_id)
    {
        return Inertia::render('Admin/Eprescriptions/Prescriptions/EditPrescription', [
            'prescription' => $this->prescriptionService->find($id),
            'appointment_id' => $appointment_id,
        ]);
    }

    public function saveAndNewPrescription()
    {
        $data = $this->prescriptionService->saveAndNewPrescription();

        return Inertia::render('Admin/Eprescriptions/Prescriptions/Create', $data);
    }

    public function saveAndNewPrescriptionCreate(CreateEprescriptionRequest $request)
    {
        $data = $request->validated();

        $prescription = $this->prescriptionService->store($data);

        return redirect()->route('prescriptions.saveandnew')->with('uuid', $prescription->uuid)->with('is_followup', $prescription->is_followup);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function quickAppointment($patient_id)
    {
        try {
            $data = $this->appointmentService->quickAppointment($patient_id);
            $appointment = $this->appointmentService->create($data);
            $templateSlug = 'appointment_confirmation';
            $this->appointmentService->appointmentMail($appointment, $templateSlug);

            return redirect()->back()->with('patient_id', $patient_id);

        } catch (\Exception $e) {

            return back()->with('error', 'Doctor does not have a chamber.');
        }
    }

    /** All Pateints */
    public function getAllPateint()
    {
        $data = $this->prescriptionService->getAllPatient();

        return $data;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function createPatient(CreatePatientRequest $request)
    {
        $data = $request->validated();
        $patientdata = $this->prescriptionpatient->create($data);

        return redirect()->back()->with('patient_id', $patientdata->id);
    }
}
