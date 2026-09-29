<?php

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use App\Http\Requests\Doctor\CreateDoctorProfileRequest;
use App\Http\Requests\Doctor\UpdateDoctorProfileRequest;
use App\Services\Doctors\DoctorService;
use App\Services\Profile\ProfileServices;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ProfileController extends Controller
{
    protected ProfileServices $profileService;

    protected DoctorService $doctorService;

    protected $user;

    public function __construct(
        ProfileServices $profileService,
        DoctorService $doctorService
    ) {
        $this->profileService = $profileService;
        $this->doctorService = $doctorService;
        $this->user = Auth::user();
    }

    public function index()
    {
        if ($this->user->hasRole('Doctor')) {
            return redirect()->route('doctor.profile');
        } elseif ($this->user->hasRole('Patient')) {
            return redirect()->route('patients.profile.create');
        } else {
            return to_route('profile.edit');
        }
    }

    public function getDoctorProfile()
    {
        $doctor = $this->profileService->getDoctorProfile();

        $specialties = $this->doctorService->getSpecialtiesForSelect();

        if ($doctor) {
            return Inertia::render('Profile/Doctor/Edit', [
                'doctor'      => $doctor,
                'specialties' => $specialties,
            ]);
        } else {
            return Inertia::render('Profile/Doctor/Create', [
                'specialties' => $specialties,
            ]);
        }
    }

    public function createDoctorProfile(CreateDoctorProfileRequest $request)
    {
        $data = $request->validated();

        $this->profileService->createDoctorProfile($data);

        return redirect()->back()
            ->with('success', 'Profile created successfully.');
    }

    public function updateDoctorProfile(UpdateDoctorProfileRequest $request)
    {
        $data = $request->validated();

        $this->profileService->updateDoctorProfile($data);

        return redirect()->back()
            ->with('success', 'Profile Updated successfully.');
    }

    public function getPatientProfile()
    {
        $patient = $this->profileService->getPatientProfile();
        if ($patient) {
            return Inertia::render('Profile/Patient/Edit', [
                'patient' => $patient,
            ]);
        } else {
            return Inertia::render('Profile/Patient/Create');
        }
    }
}
