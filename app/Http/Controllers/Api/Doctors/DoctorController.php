<?php

namespace App\Http\Controllers\Api\Doctors;

use App\Http\Controllers\Controller;
use App\Http\Requests\Doctor\CreateDoctorProfileRequest;
use App\Http\Requests\Doctor\UpdateDoctorProfileRequest;
use App\Http\Resources\Doctors\DoctorResource;
use App\Services\ApiResponseService;
use App\Services\Doctors\DoctorEducationService;
use App\Services\Doctors\DoctorExperienceService;
use App\Services\Doctors\DoctorService;
use App\Services\Profile\ProfileServices;

class DoctorController extends Controller
{
    public function __construct(
        protected ProfileServices $profileService,
        protected DoctorService $doctorService,
        protected DoctorExperienceService $doctorExperienceService,
        protected DoctorEducationService $DoctorEducationService,
    ) {}

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
        $doctor = $this->profileService->getDoctorProfile();
        $this->doctorService->delete($doctor);

        return ApiResponseService::success([], 'Doctor Deleted successfully.');
    }
}
