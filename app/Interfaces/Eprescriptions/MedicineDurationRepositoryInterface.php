<?php

namespace App\Interfaces\Eprescriptions;

use App\Models\MedicineDuration;

interface MedicineDurationRepositoryInterface
{
    public function all();

    public function paginate($search, int $perPage = 15);

    public function find(int $id): ?MedicineDuration;

    public function create(array $data): MedicineDuration;

    public function update(int $id, array $data): bool;

    public function delete(int $id): bool;
}
