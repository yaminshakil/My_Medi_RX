<?php

namespace App\Repositories\Doctors;

use App\Interfaces\Doctors\DoctorExperienceRepositoryInterface;
use App\Models\DoctorExperience;

class DoctorExperienceRepository implements DoctorExperienceRepositoryInterface
{
    public function allForDoctor(int $doctorId)
    {
        return DoctorExperience::where('doctor_id', $doctorId)->get();
    }

    public function find(int $id): ?DoctorExperience
    {
        return DoctorExperience::find($id);
    }

    public function create(array $data): DoctorExperience
    {
        return DoctorExperience::create($data);
    }

    public function update(int $id, array $data): bool
    {
        $experience = $this->find($id);

        return $experience ? $experience->update($data) : false;
    }

    public function delete(int $id): bool
    {
        $experience = $this->find($id);

        return $experience ? $experience->delete() : false;
    }
}
