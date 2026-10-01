<?php

namespace App\Http\Controllers\APi\Patients;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Patients\CreatePatientProfileRequest;
use App\Http\Requests\Patients\UpdatePatientProfileRequest;
use App\Services\Patients\PatientProfileService;
use App\Services\ApiResponseService;
use App\Http\Resources\Patients\PatientCollection;
use App\Http\Resources\Patients\PatientResource;
use App\Models\Setting;
use App\Services\Patients\PatientService;

class PatientController extends Controller
{
    protected $patients;
    protected $patientService;

    public function __construct(
        PatientProfileService $patients,
        PatientService $patientService
    ) {
        $this->patients = $patients;
        $this->patientService = $patientService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $recordsPerPage = Setting::getValue('records_per_page', 10);

        $search = $request->filled('search') ? $request->search : null;
        $sortBy = $request->input('sort_by', 'created_at');
        $sortDirection = $request->input('sort_direction', 'desc');
        $perPage = $request->input('per_page', $recordsPerPage);

        // Pass Eloquent collection/paginator directly
        $patients = $this->patientService->all($search, $sortBy, $sortDirection, $perPage);

        return ApiResponseService::success(
            new PatientCollection($patients),
            'Patients retrieved successfully!'
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreatePatientProfileRequest $request)
    {
        $data = $request->validated();

        $patientData = $this->patients->create($data);

        return ApiResponseService::success(new PatientResource($patientData), 'Patient Profile created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $patientData = $this->patients->find($id);

        if (!$patientData) {
            return ApiResponseService::error('Patient not found', 404);
        }

        return ApiResponseService::success(new PatientResource($patientData), 'Patient Profile retrieved successfully!');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePatientProfileRequest $request, string $id)
    {
        $data = $request->validated();

        $patientUpdated = $this->patients->update($id, $data);

        if (!$patientUpdated) {
            return ApiResponseService::error('Patient Profile not found', 404);
        }

        $patientData = $this->patients->find($id);

        return ApiResponseService::success(new PatientResource($patientData), 'Patient Profile updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $this->patients->delete($id);
        return ApiResponseService::success([], 'Patient Profile deleted successfully!');
    }
}
