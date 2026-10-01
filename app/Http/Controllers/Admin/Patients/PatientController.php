<?php

namespace App\Http\Controllers\Admin\Patients;

use App\Http\Controllers\Controller;
use App\Http\Requests\Patients\CreatePatientRequest;
use App\Http\Requests\Patients\UpdatePatientRequest;
use App\Models\Setting;
use App\Services\Patients\PatientService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PatientController extends Controller
{
    protected $patientService;

    public function __construct(PatientService $patientService)
    {
        $this->patientService = $patientService;
    }

    public function index(Request $request)
    {
        $recordsPerPage = Setting::getValue('records_per_page', 10);

        if ($request->filled('search')) {
            $search = $request->search;
        } else {
            $search = null;
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'created_at');
        $sortDirection = $request->input('sort_direction', 'desc');

        $perPage = $request->input('per_page', $recordsPerPage); // default 10

        $patients = $this->patientService->all($search, $sortBy, $sortDirection, $perPage);

        $patients->getCollection()->transform(fn ($patient) => [
            'id' => $patient->id,
            'name' => $patient->name,
            'phone' => $patient->phone,
            'patient_number' => $patient->patient_number,
            'gender' => ucfirst(strtolower($patient->gender)),
            'address' => $patient->address,
            'city' => $patient->city,
            'vitals' => $patient->vitals()->latest()->take(1)->get(),
            'prescriptions' => $patient->prescriptions,
            'created_at' => $patient->created_at->format('Y-m-d'),
        ]);

        return Inertia::render('Admin/Patients/Index', [
            'patients' => $patients,
            'filters' => $request->only(['search', 'per_page', 'sort_by', 'sort_direction']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Patients/Create');
    }

    public function store(CreatePatientRequest $request)
    {
        $data = $request->validated();

        $this->patientService->create($data);

        return redirect()->route('patients.index')->with('success', 'Patient created successfully');
    }

    public function show($id)
    {
        $patient = $this->patientService->find($id);
        $patient->load('user', 'vitals', 'prescriptions');

        return Inertia::render('Admin/Patients/Show', [
            'patient' => $patient,
        ]);
    }

    public function edit($id)
    {
        $patient = $this->patientService->find($id);
        $patient->load('user');

        return Inertia::render('Admin/Patients/Edit', [
            'patient' => $patient,
        ]);
    }

    public function update(UpdatePatientRequest $request, $id)
    {
        $data = $request->validated();
        $this->patientService->update($id, $data);

        return redirect()->route('patients.index')->with('success', 'Patient updated successfully');
    }

    public function destroy($id)
    {
        $this->patientService->delete($id);

        return redirect()->route('patients.index')->with('success', 'Patient deleted successfully');
    }
}
