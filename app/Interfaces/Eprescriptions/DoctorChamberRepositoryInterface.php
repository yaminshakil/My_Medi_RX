<?php

namespace App\Interfaces\Eprescriptions;

use App\Models\DoctorChamber;
use Illuminate\Database\Eloquent\Collection;

interface DoctorChamberRepositoryInterface
{
    public function all(): Collection;

    public function create(array $data): ?DoctorChamber;

    public function update(array $data, int $id): int;

    public function delete(int $id): bool;

    public function find(int $id): ?DoctorChamber;

    public function switchChamber($chamber_id);
}
