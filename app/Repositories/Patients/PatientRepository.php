<?php

namespace App\Repositories\Patients;

use App\Interfaces\Patients\PatientRepositoryInterface;
use App\Models\Patient;
use App\Models\User;
use App\Services\Patients\PatientNumberService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PatientRepository implements PatientRepositoryInterface
{
    protected $patientnumber;

    protected $user;

    public function __construct(PatientNumberService $patientnumber)
    {
        $this->patientnumber = $patientnumber;
        $this->user = Auth::user();
    }

    public function all($search, $sortBy, $sortDirection, $perPage): LengthAwarePaginator
    {
        $patients = Patient::query()
        // Eager load relations to avoid N+1 queries
        ->with([
            'vitals' => fn ($query) => $query->latest()->limit(1),
            'prescriptions',
            'profile_image',
        ]);

        if ($search) {
            $patients->where(
                fn ($query) => $query->where('name', 'like', "%{$search}%")
                    ->orWhere('patient_number', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%")
            );
        }

        if (in_array($sortBy, ['patient_number', 'name', 'phone', 'gender', 'address', 'city', 'created_at'])) {
            $patients->orderBy($sortBy, $sortDirection);
        } else {
            $patients->latest();
        }

        return $patients->paginate($perPage)->withQueryString();
    }

    public function find(int $id): ?Patient
    {
        return Patient::find($id);
    }

    public function create(array $data): Patient
    {
        $user = User::where('mobile', $data['phone'])->orwhere('email', $data['email'])->first();
        if ($user?->id) {
            $data += ['user_id' => $user->id];
        }

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
                Storage::disk('public')->delete($this->user->profileImage->path);
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

        return $patient ? (bool) $patient->delete() : false;
    }
}
