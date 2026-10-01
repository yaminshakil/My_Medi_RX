<?php

namespace App\Interfaces\Eprescriptions;

use App\Models\Manufacturer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ManufacturerRepositoryInterface
{
    public function all($perPage, $search): LengthAwarePaginator;

    public function find(int $id): ?Manufacturer;

    public function store(array $data): Manufacturer;

    public function update(int $id, array $data): bool;

    public function delete(int $id): bool;
}
