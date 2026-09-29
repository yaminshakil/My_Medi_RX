<?php

namespace App\Http\Controllers\Api\Doctors;

use App\Http\Controllers\Controller;
use App\Http\Requests\Doctor\CreateEducationRequest;
use App\Http\Requests\Doctor\UpdateEducationRequest;
use App\Services\ApiResponseService;
use App\Services\Doctors\DoctorEducationService;

class DoctorEducationController extends Controller
{
    protected $educationService;

    public function __construct(DoctorEducationService $educationService)
    {
        $this->educationService = $educationService;
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
    public function store(CreateEducationRequest $request)
    {
        $request->validated();

        $doctorId = $request->doctor_id;

        $doctorData = $this->educationService->createForDoctor($doctorId, $request->only([
            'degree', 'institute', 'year', 'country',
        ]));

        return ApiResponseService::success($doctorData, 'Education added successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateEducationRequest $request, string $id)
    {
        $request->validated();

        $doctorData = $this->educationService->update($id, $request->only([
            'degree', 'institute', 'year', 'country',
        ]));

        $doctorData = $this->educationService->listByDoctor($request->doctor_id);

        return ApiResponseService::success($doctorData, 'Education updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $doctorData = $this->educationService->delete($id);

        return ApiResponseService::success($doctorData, 'Education deleted successfully.');
    }
}
