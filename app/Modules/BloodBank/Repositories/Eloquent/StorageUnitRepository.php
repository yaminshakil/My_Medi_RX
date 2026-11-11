<?php

namespace App\Modules\BloodBank\Repositories\Eloquent;

use App\Modules\BloodBank\Models\StorageUnit;
use App\Modules\BloodBank\Repositories\Contracts\StorageUnitRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class StorageUnitRepository implements StorageUnitRepositoryInterface
{
    public function active(): Collection
    {
        return StorageUnit::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'type', 'hospital_id']);
    }

    public function all(): Collection
    {
        return StorageUnit::query()->orderBy('name')->orderBy('id')->get();
    }

    public function paginate(int $perPage = 20): LengthAwarePaginator
    {
        return StorageUnit::query()
            ->with('hospital:id,hospital_name')
            ->withCount('bags')
            ->orderBy('name')
            ->orderBy('id')
            ->paginate($perPage);
    }

    public function create(array $data): StorageUnit
    {
        return StorageUnit::query()->create($data);
    }

    public function update(StorageUnit $storageUnit, array $data): StorageUnit
    {
        $storageUnit->update($data);

        return $storageUnit;
    }

    public function delete(StorageUnit $storageUnit): void
    {
        $storageUnit->delete();
    }
}
