<?php

namespace App\Interfaces\Eprescriptions;

use App\Models\MedicineDose;

interface MedicineDoseRepositoryInterface
{
    public function all($perPage, $search);

    public function find(int $id): ?MedicineDose;

    public function create(array $data): MedicineDose;

    public function update(int $id, array $data): bool;

    public function delete(int $id): bool;
}
