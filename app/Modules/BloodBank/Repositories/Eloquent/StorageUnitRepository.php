<?php

namespace App\Modules\BloodBank\Repositories\Eloquent;

use App\Modules\BloodBank\Models\StorageUnit;
use App\Modules\BloodBank\Repositories\Contracts\StorageUnitRepositoryInterface;
use Illuminate\Support\Collection;

class StorageUnitRepository implements StorageUnitRepositoryInterface
{
    public function active(): Collection
    {
        return StorageUnit::query()->where('is_active', true)->orderBy('name')->get(['id', 'name', 'type']);
    }
}
