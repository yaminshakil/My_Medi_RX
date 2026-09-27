<?php

namespace App\Services\Doctors;

use App\Interfaces\Doctors\DoctorEducationRepositoryInterface;

class DoctorEducationService
{
    protected $educationRepo;

    public function __construct(DoctorEducationRepositoryInterface $educationRepo)
    {
        $this->educationRepo = $educationRepo;
    }

    public function listByDoctor(int $doctorId)
    {
        return $this->educationRepo->allForDoctor($doctorId);
    }

    public function createForDoctor(int $doctorId, array $data)
    {
        $data['doctor_id'] = $doctorId;

        return $this->educationRepo->create($data);
    }

    public function update(int $id, array $data)
    {
        return $this->educationRepo->update($id, $data);
    }

    public function delete(int $id)
    {
        return $this->educationRepo->delete($id);
    }
}
