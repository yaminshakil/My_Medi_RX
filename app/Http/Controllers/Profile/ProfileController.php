<?php

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use App\Http\Requests\Doctor\CreateDoctorProfileRequest;
use App\Http\Requests\Doctor\UpdateDoctorProfileRequest;
use App\Modules\BloodBank\Models\BloodDonor;
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
        $id = $this->user->id;
        $doctor = $this->profileService->getDoctorProfile($id);

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
            $donor = BloodDonor::query()
                ->where('user_id', $this->user->id)
                ->first([
                    'id',
                    'name',
                    'phone',
                    'blood_group',
                    'latitude',
                    'longitude',
                    'city',
                    'last_donated_at',
                    'is_verified',
                    'is_available',
                ]);

            return Inertia::render('Profile/Patient/Edit', [
                'patient' => $patient,
                'donor'   => $donor ? [
                    'id'              => $donor->id,
                    'name'            => $donor->name,
                    'phone'           => $donor->phone,
                    'blood_group'     => $donor->blood_group->value,
                    'latitude'        => $donor->latitude,
                    'longitude'       => $donor->longitude,
                    'city'            => $donor->city,
                    'last_donated_at' => $donor->last_donated_at?->toDateString(),
                    'is_verified'     => $donor->is_verified,
                    'is_available'    => $donor->is_available,
                ] : null,
            ]);
        } else {
            return Inertia::render('Profile/Patient/Create');
        }
    }
}
