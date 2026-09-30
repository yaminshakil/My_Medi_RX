<?php

namespace App\Interfaces\Patients;

use App\Models\Patient;

interface PatientProfileRepositoryInterface
{
    public function find(int $id): ?Patient;

    public function create(array $data): Patient;

    public function update(int $id, array $data): bool;

    public function delete(int $id): bool;
}
