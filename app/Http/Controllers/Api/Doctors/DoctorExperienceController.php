<?php

namespace App\Http\Controllers\Api\Doctors;

use App\Http\Controllers\Controller;
use App\Http\Requests\Doctor\CreateExperienceRequest;
use App\Http\Requests\Doctor\UpdateExperienceRequest;
use App\Services\Doctors\DoctorExperienceService;
use Illuminate\Http\Request;
use App\Services\ApiResponseService;

class DoctorExperienceController extends Controller
{
    protected $experienceService;

    public function __construct(DoctorExperienceService $experienceService)
    {
        $this->experienceService = $experienceService;
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
    public function store(Request $request)
    {
        $data = $request->validated();

        $doctorId = $request->doctor_id;

        $doctorData = $this->experienceService->createForDoctor($doctorId, $data);

        return ApiResponseService::success($doctorData, 'Experience added successfully.');
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
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
