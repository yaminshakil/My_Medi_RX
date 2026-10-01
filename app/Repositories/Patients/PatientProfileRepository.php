<?php

namespace App\Repositories\Patients;

use App\Interfaces\Patients\PatientProfileRepositoryInterface;
use App\Models\Patient;
use App\Services\Patients\PatientNumberService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PatientProfileRepository implements PatientProfileRepositoryInterface
{
    protected $patientnumber;

    protected $user;

    public function __construct(PatientNumberService $patientnumber)
    {
        $this->patientnumber = $patientnumber;
        $this->user = Auth::user();
    }

    public function find(int $id): ?Patient
    {
        $patient = Patient::with('user', 'user.profileImage')->find($id);

        return $patient;
    }

    public function create(array $data): Patient
    {
        $data += ['user_id' => $this->user->id];

        $patientnumber = $this->patientnumber->generate();
        $data += ['patient_number' => $patientnumber];

        $patient = Patient::create($data);

        if (isset($data['profile_image'])) {
            $profileImagePath = $data['profile_image']->store('profiles', 'public');
            $patient->profileImage()->create([
                'path' => $profileImagePath,
            ]);
        }

        return $patient;
    }

    public function update(int $id, array $data): bool
    {
        $patient = $this->find($id);
        if (isset($data['profile_image'])) {
            // Delete old image if exists
            if ($patient->profileImage) {
                Storage::disk('public')->delete($patient->profileImage->path);
                $patient->profileImage()->delete();
            }

            // Store new
            $path = $data['profile_image']->store('profiles', 'public');

            $patient->profileImage()->create([
                'path' => $path,
            ]);
        }

        return $patient ? $patient->update($data) : false;
    }

    public function delete(int $id): bool
    {
        $patient = $this->find($id);
        if ($patient && $patient->profileImage) {
            Storage::disk('public')->delete($patient->profileImage->path);
            $patient->profileImage()->delete();
        }
        return $patient ? (bool) $patient->delete() : false;
    }
}
