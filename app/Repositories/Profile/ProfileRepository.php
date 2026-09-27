<?php

namespace App\Repositories\Profile;

use App\Interfaces\Profile\ProfileRepositoryInterface;
use App\Models\Doctor;
use App\Models\Patient;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProfileRepository implements ProfileRepositoryInterface
{
    protected Doctor $doctor;

    protected Patient $patient;

    protected $user_id;

    protected $user;

    public function __construct(Doctor $doctor, Patient $patient)
    {
        $this->doctor = $doctor;
        $this->patient = $patient;
        $this->user_id = Auth::user()->id;
        $this->user = Auth::user();
    }

    public function getDoctorProfile()
    {
        return $this->doctor::with('user', 'user.specialties', 'user.profileImage')
            ->where('user_id', $this->user_id)->first();
    }

    public function createDoctorProfile(array $data)
    {
        if (isset($data['profile_image'])) {
            $profileImagePath = $data['profile_image']->store('profiles', 'public');
            $this->user->profileImage()->create([
                'path' => $profileImagePath,
            ]);
        }

        $doctor = Doctor::create([
            'user_id'           => $this->user_id,
            'phone'             => $data['phone'] ?? null,
            'gender'            => $data['gender'] ?? null,
            'dob'               => $data['dob'] ?? null,
            'specialization'    => $data['specialization'] ?? null,
            'working_institute' => $data['working_institute'] ?? null,
            'designation'       => $data['designation'] ?? null,
            'qualification'     => $data['qualification'] ?? null,
            'registration_no'   => $data['registration_no'] ?? null,
            'experience_years'  => $data['experience_years'] ?? null,
            'bio'               => $data['bio'] ?? null,
            'social'            => $data['social'],
            'active'            => $data['active'] ?? true,
        ]);

        $this->user->specialties()->sync($data['specialization_ids']);

        return $doctor;
    }

    public function updateDoctorProfile(array $data)
    {
        $doctor = Doctor::where('user_id', $this->user_id)->first();

        if (isset($data['profile_image'])) {
            // Delete old image if exists
            if ($this->user->profileImage) {
                Storage::disk('public')->delete($this->user->profileImage->path);
                $this->user->profileImage()->delete();
            }

            // Store new
            $path = $data['profile_image']->store('profiles', 'public');

            $this->user->profileImage()->create([
                'path' => $path,
            ]);
        }

        $doctor = $doctor->update([
            'phone'             => $data['phone'] ?? null,
            'gender'            => $data['gender'] ?? null,
            'dob'               => $data['dob'] ?? null,
            'specialization'    => $data['specialization'] ?? null,
            'working_institute' => $data['working_institute'] ?? null,
            'designation'       => $data['designation'] ?? null,
            'qualification'     => $data['qualification'] ?? null,
            'registration_no'   => $data['registration_no'] ?? null,
            'experience_years'  => $data['experience_years'] ?? null,
            'bio'               => $data['bio'] ?? null,
            'social'            => $data['social'],
            'active'            => $data['active'] ?? true,
        ]);

        $this->user->specialties()->sync($data['specialization_ids']);

        return $doctor;
    }



    public function getPatientProfile()
    {
        return $this->patient::with('user', 'user.profileImage')->where('user_id', $this->user_id)->first();
    }
}
