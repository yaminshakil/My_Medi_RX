<?php

namespace App\Interfaces\Doctors;

use App\Models\DoctorEducation;

interface DoctorEducationRepositoryInterface
{
    public function allForDoctor(int $doctorId);

    public function find(int $id): ?DoctorEducation;

    public function create(array $data): DoctorEducation;

    public function update(int $id, array $data): bool;

    public function delete(int $id): bool;
}
