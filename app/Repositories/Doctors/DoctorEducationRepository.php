<?php

namespace App\Repositories\Doctors;

use App\Interfaces\Doctors\DoctorEducationRepositoryInterface;
use App\Models\DoctorEducation;

class DoctorEducationRepository implements DoctorEducationRepositoryInterface
{
    public function allForDoctor(int $doctorId)
    {
        return DoctorEducation::where('doctor_id', $doctorId)->get();
    }

    public function find(int $id): ?DoctorEducation
    {
        return DoctorEducation::find($id);
    }

    public function create(array $data): DoctorEducation
    {
        return DoctorEducation::create($data);
    }

    public function update(int $id, array $data): bool
    {
        $education = $this->find($id);

        return $education ? $education->update($data) : false;
    }

    public function delete(int $id): bool
    {
        $education = $this->find($id);

        return $education ? $education->delete() : false;
    }
}
