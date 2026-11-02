<?php

namespace App\Modules\BloodBank\Repositories\Eloquent;

use App\Modules\BloodBank\Models\Physician;
use App\Modules\BloodBank\Repositories\Contracts\PhysicianRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class PhysicianRepository implements PhysicianRepositoryInterface
{
    public function active(): Collection
    {
        return Physician::query()->where('is_active', true)->orderBy('name')->get(['id', 'name', 'department']);
    }

    public function paginate(array $filters, int $perPage = 20): LengthAwarePaginator
    {
        return Physician::query()
            ->when($filters['search'] ?? null, fn ($q, $s) => $q->where(fn ($w) => $w
                ->where('name', 'like', "%{$s}%")
                ->orWhere('license_no', 'like', "%{$s}%")
                ->orWhere('department', 'like', "%{$s}%")))
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function create(array $data): Physician
    {
        return Physician::create($data);
    }

    public function setActive(Physician $physician, bool $active): Physician
    {
        $physician->update(['is_active' => $active]);

        return $physician;
    }
}
