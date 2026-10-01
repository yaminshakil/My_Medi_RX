<?php

namespace App\Interfaces\Eprescriptions;

use App\Models\Appointment;

interface AppointmentRepositoryInterface
{
    public function paginate($search, $perPage, $sortBy, $sortDirection, $date, $status);

    public function create(array $data): ?Appointment;

    public function update(array $data, int $id): int;

    public function delete(int $id): bool;

    public function find(int $id): ?Appointment;

    public function quickAppointment($patient_id);

    public function appointmentMail($appointment, $templateSlug);
}
