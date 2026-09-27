<?php

namespace App\Interfaces\Doctors;

use App\Models\Doctor;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

interface DoctorRepositoryInterface
{
    public function all(): Collection;

    public function create(array $data): ?Doctor;

    public function update(array $data, Doctor $doctor): int;

    public function delete(Doctor $doctor): bool;

    public function find(int $id): ?User;

    public function getSpecialtiesForSelect();

    public function getAvailabilityFromTimeSlots($chambers);

    public function processAvailability($timeSlots);
}
