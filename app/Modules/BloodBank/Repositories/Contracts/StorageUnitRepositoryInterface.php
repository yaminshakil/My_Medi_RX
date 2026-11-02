<?php

namespace App\Modules\BloodBank\Repositories\Contracts;

use App\Modules\BloodBank\Models\StorageUnit;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface StorageUnitRepositoryInterface
{
    public function active(): Collection;

    public function all(): Collection;

    public function paginate(int $perPage = 20): LengthAwarePaginator;

    public function create(array $data): StorageUnit;

    public function update(StorageUnit $storageUnit, array $data): StorageUnit;

    public function delete(StorageUnit $storageUnit): void;
}
