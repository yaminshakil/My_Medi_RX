<?php

namespace App\Services\Doctors;

use App\Interfaces\Doctors\DoctorRepositoryInterface;
use App\Models\Doctor;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class DoctorService
{
    /**
     * Create a new class instance.
     */
    public function __construct(protected DoctorRepositoryInterface $doctorRepository)
    {
        //
    }

    public function create(array $data): Doctor
    {
        return $this->doctorRepository->create($data);
    }

    public function update(array $data, Doctor $doctor): int
    {
        return $this->doctorRepository->update($data, $doctor);
    }

    public function delete(Doctor $doctor): bool
    {
        return $this->doctorRepository->delete($doctor);
    }

    public function all(): Collection
    {
        return $this->doctorRepository->all();
    }

    public function find(int $id): ?User
    {
        return $this->doctorRepository->find($id);
    }

    public function getSpecialtiesForSelect()
    {
        return $this->doctorRepository->getSpecialtiesForSelect();
    }

    public function getAvailabilityFromTimeSlots($chambers)
    {
        return $this->doctorRepository->getAvailabilityFromTimeSlots($chambers);
    }

    public function processAvailability($timeSlots)
    {
        return $this->doctorRepository->processAvailability($timeSlots);
    }
}
