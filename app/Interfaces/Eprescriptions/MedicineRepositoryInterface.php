<?php

namespace App\Interfaces\Eprescriptions;

use App\Models\Medicine;

interface MedicineRepositoryInterface
{
    public function all($perPage, $search);

    public function find(int $id): ?Medicine;

    public function create(array $data): Medicine;

    public function update(int $id, array $data): bool;

    public function delete(int $id): bool;

    public function getManufacturer();
}
