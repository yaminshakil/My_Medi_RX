<?php

namespace App\Interfaces\Doctors;

use App\Models\DoctorExperience;

interface DoctorExperienceRepositoryInterface
{
    public function allForDoctor(int $doctorId);

    public function find(int $id): ?DoctorExperience;

    public function create(array $data): DoctorExperience;

    public function update(int $id, array $data): bool;

    public function delete(int $id): bool;
}
