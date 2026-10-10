<?php

namespace App\Services\Eprescriptions;

use App\Interfaces\Eprescriptions\AppointmentRepositoryInterface;
use App\Models\Appointment;

class AppointmentService
{
    protected AppointmentRepositoryInterface $repository;

    /**
     * Create a new class instance.
     */
    public function __construct(AppointmentRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function paginate($search, $perPage, $sortBy, $sortDirection, $date, $status)
    {
        return $this->repository->paginate($search, $perPage, $sortBy, $sortDirection, $date, $status);
    }

    public function create(array $data): ?Appointment
    {
        return $this->repository->create($data);
    }

    public function update(array $data, int $id): int
    {
        return $this->repository->update($data, $id);
    }

    public function delete(int $id): bool
    {
        return $this->repository->delete($id);
    }

    public function find(int $id): ?Appointment
    {
        return $this->repository->find($id);
    }

    public function quickAppointment($patient_id)
    {
        return $this->repository->quickAppointment($patient_id);
    }

    public function appointmentMail($appointment, $templateSlug)
    {
        return $this->repository->appointmentMail($appointment, $templateSlug);
    }
}
