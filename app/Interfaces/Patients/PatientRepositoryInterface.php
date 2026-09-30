<?php

namespace App\Interfaces\Patients;

use App\Models\Patient;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface PatientRepositoryInterface
{
    public function all($search, $sortBy, $sortDirection, $perPage): LengthAwarePaginator;

    public function find(int $id): ?Patient;

    public function create(array $data): Patient;

    public function update(int $id, array $data): bool;

    public function delete(int $id): bool;
}
