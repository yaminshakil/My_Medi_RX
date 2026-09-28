<?php

namespace App\Http\Controllers\Api\Doctors;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\Doctors\DoctorResource;
use App\Http\Requests\Doctor\CreateDoctorProfileRequest;
use App\Http\Requests\Doctor\UpdateDoctorProfileRequest;
use App\Services\Profile\ProfileServices;
use App\Http\Requests\Doctor\CreateExperienceRequest;
use App\Http\Requests\Doctor\UpdateExperienceRequest;
use App\Services\Doctors\DoctorExperienceService;
use App\Services\ApiResponseService;

class DoctorController extends Controller
{
    public function __construct(
        protected ProfileServices $profileService
    ) {
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
    public function store(CreateDoctorProfileRequest $request)
    {
        $data = $request->validated();

        $doctor = $this->profileService->createDoctorProfile($data);

        $doctorData = new DoctorResource($doctor);

        return ApiResponseService::success($doctorData, 'Profile created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $doctor = $this->profileService->getDoctorProfile();
        $doctorData = new DoctorResource($doctor);

        return ApiResponseService::success($doctorData, 'Profile retrived successfully!');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDoctorProfileRequest $request, string $id)
    {
        $data = $request->validated();

        $doctor = $this->profileService->updateDoctorProfile($data);
        $doctor = $this->profileService->getDoctorProfile();

        $doctorData = new DoctorResource($doctor);

        return ApiResponseService::success($doctorData, 'Profile created successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
