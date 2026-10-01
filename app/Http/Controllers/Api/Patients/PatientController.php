<?php

namespace App\Http\Controllers\APi\Patients;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Patients\CreatePatientProfileRequest;
use App\Http\Requests\Patients\UpdatePatientProfileRequest;
use App\Services\Patients\PatientProfileService;
use App\Services\ApiResponseService;
use App\Http\Resources\Patients\PatientResource;

class PatientController extends Controller
{
    protected $patients;

    public function __construct(
        PatientProfileService $patients,
    ) {
        $this->patients = $patients;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
        //
    }
}
