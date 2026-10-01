<?php

namespace App\Services\Patients;

use App\Interfaces\Patients\PatientProfileRepositoryInterface;
use App\Models\Patient;

class PatientProfileService
{
    protected $patients;

    /**
     * Create a new class instance.
     */
    public function __construct(PatientProfileRepositoryInterface $patients)
    {
        $this->patients = $patients;
    }

    public function find(int $id): ?Patient
    {
        return $this->patients->find($id);
    }

    public function create($data)
    {
        return $this->patients->create($data);
    }

    public function update(int $id, $data)
    {
        return $this->patients->update($id, $data);
    }

    public function delete(int $id)
    {
        return $this->patients->delete($id);
    }
}
