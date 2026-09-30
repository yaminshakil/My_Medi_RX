<?php

namespace App\Services\Patients;

use App\Interfaces\Patients\PatientRepositoryInterface;
use App\Models\Patient;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PatientService
{
    protected $patientRepository;

    /**
     * Create a new class instance.
     */
    public function __construct(PatientRepositoryInterface $patientRepository)
    {
        $this->patientRepository = $patientRepository;
    }

    public function all($search, $sortBy, $sortDirection, $perPage = 10): LengthAwarePaginator
    {
        return $this->patientRepository->all($search, $sortBy, $sortDirection, $perPage);
    }

    public function find(int $id): ?Patient
    {
        return $this->patientRepository->find($id);
    }

    public function create(array $data): Patient
    {
        return $this->patientRepository->create($data);
    }

    public function update(int $id, array $data): bool
    {
        return $this->patientRepository->update($id, $data);
    }

    public function delete(int $id): bool
    {
        return $this->patientRepository->delete($id);
    }
}
