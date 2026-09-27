<?php

namespace App\Repositories\MedicalSpecialty;

use App\Interfaces\MedicalSpecialty\MedicalSpecialtyRepositoryInterface;
use App\Models\MedicalSpecialty;

class MedicalSpecialtyRepository implements MedicalSpecialtyRepositoryInterface
{
    public function all($search, $perPage)
    {
        $medicalSpecialty = MedicalSpecialty::query();
        if ($search) {
            $medicalSpecialty->where(
                fn ($query) => $query->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
            );
        }
        $medicalSpecialty = $medicalSpecialty->with('parent')->latest()->paginate($perPage)->withQueryString();

        return $medicalSpecialty;
    }

    public function find($id): ?MedicalSpecialty
    {
        return MedicalSpecialty::find($id);
    }

    public function create(array $data): MedicalSpecialty
    {
        return MedicalSpecialty::create($data);
    }

    public function update(MedicalSpecialty $specialty, array $data): MedicalSpecialty
    {
        $specialty->update($data);

        return $specialty;
    }

    public function delete(MedicalSpecialty $specialty): bool
    {
        return $specialty->delete();
    }
}
