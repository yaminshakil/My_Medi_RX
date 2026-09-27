<?php

namespace App\Services\Doctors;

use App\Interfaces\Doctors\DoctorExperienceRepositoryInterface;

class DoctorExperienceService
{
    protected $experienceRepo;

    public function __construct(DoctorExperienceRepositoryInterface $experienceRepo)
    {
        $this->experienceRepo = $experienceRepo;
    }

    public function listByDoctor(int $doctorId)
    {
        return $this->experienceRepo->allForDoctor($doctorId);
    }

    public function createForDoctor(int $doctorId, array $data)
    {
        $data['doctor_id'] = $doctorId;

        return $this->experienceRepo->create($data);
    }

    public function update(int $id, array $data)
    {
        return $this->experienceRepo->update($id, $data);
    }

    public function delete(int $id)
    {
        return $this->experienceRepo->delete($id);
    }
}
