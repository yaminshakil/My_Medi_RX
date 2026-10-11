<?php

namespace App\Modules\BloodBank\Repositories\Eloquent;

use App\Modules\BloodBank\Models\Physician;
use App\Modules\BloodBank\Repositories\Contracts\PhysicianRepositoryInterface;
use Illuminate\Support\Collection;

class PhysicianRepository implements PhysicianRepositoryInterface
{
    public function active(): Collection
    {
        return Physician::query()->where('is_active', true)->orderBy('name')->get(['id', 'name', 'department']);
    }
}
