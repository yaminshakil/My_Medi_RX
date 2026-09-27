<?php

namespace App\Interfaces\MedicalSpecialty;

use App\Models\MedicalSpecialty;

interface MedicalSpecialtyRepositoryInterface
{
    public function all($search, $perPage);

    public function find($id): ?MedicalSpecialty;

    public function create(array $data): MedicalSpecialty;

    public function update(MedicalSpecialty $specialty, array $data): MedicalSpecialty;

    public function delete(MedicalSpecialty $specialty): bool;
}
