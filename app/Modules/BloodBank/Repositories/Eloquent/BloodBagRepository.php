<?php

namespace App\Modules\BloodBank\Repositories\Eloquent;

use App\Modules\BloodBank\Enums\BagStatus;
use App\Modules\BloodBank\Enums\BloodComponent;
use App\Modules\BloodBank\Models\BloodBag;
use App\Modules\BloodBank\Repositories\Contracts\BloodBagRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class BloodBagRepository implements BloodBagRepositoryInterface
{
    // Only these columns may be sorted on (the sort value comes from the query string).
    private const SORTABLE = ['expires_at', 'collected_at', 'bag_number', 'blood_group', 'created_at'];

    public function paginate(array $filters, int $perPage = 20): LengthAwarePaginator
    {
        $sort = in_array($filters['sort'] ?? null, self::SORTABLE, true) ? $filters['sort'] : 'expires_at';
        $direction = ($filters['direction'] ?? 'asc') === 'desc' ? 'desc' : 'asc';

        return BloodBag::query()
            ->with(['storageUnit:id,name', 'donor:id,name'])
            ->when($filters['search'] ?? null, fn ($q, $s) => $q->where(
                fn ($w) => $w->where('bag_number', 'like', "%{$s}%")->orWhere('batch_number', 'like', "%{$s}%")
            ))
            ->when($filters['blood_group'] ?? null, fn ($q, $v) => $q->where('blood_group', $v))
            ->when($filters['component'] ?? null, fn ($q, $v) => $q->where('component', $v))
            ->when($filters['status'] ?? null, fn ($q, $v) => $q->where('status', $v))
            ->when($filters['storage_unit_id'] ?? null, fn ($q, $v) => $q->where('storage_unit_id', $v))
            ->when(($filters['expiry'] ?? null) === 'expired', fn ($q) => $q->where('expires_at', '<=', now()))
            ->when(($filters['expiry'] ?? null) === 'expiring', fn ($q) => $q
                ->where('status', BagStatus::Available->value)
                ->whereBetween('expires_at', [now(), now()->addDays(config('blood-bank.expiring_filter_days'))]))
            ->orderBy($sort, $direction)
            ->paginate($perPage)
            ->withQueryString();
    }

    public function create(array $data): BloodBag
    {
        return BloodBag::create($data);
    }

    public function update(BloodBag $bag, array $data): BloodBag
    {
        $bag->update($data);

        return $bag;
    }

    public function lockAvailable(int $id): ?BloodBag
    {
        return BloodBag::query()
            ->whereKey($id)
            ->where('status', BagStatus::Available->value)
            ->lockForUpdate()
            ->first();
    }

    public function stockMatrix(): Collection
    {
        return BloodBag::query()
            ->available()
            ->selectRaw('blood_group, component, COUNT(*) AS units, SUM(volume_ml) AS volume_ml')
            ->groupBy('blood_group', 'component')
            ->get();
    }

    public function expiringSoon(array $windowsByComponent, int $limit = 50): Collection
    {
        return BloodBag::query()
            ->available()
            ->with('storageUnit:id,name')
            ->where(function ($q) use ($windowsByComponent) {
                // One OR-branch per component, because platelets (5 days) and plasma (1 year) need different windows.
                foreach ($windowsByComponent as $component => $days) {
                    $q->orWhere(fn ($w) => $w
                        ->where('component', $component)
                        ->where('expires_at', '<=', now()->addDays($days)));
                }
            })
            ->orderBy('expires_at')
            ->limit($limit)
            ->get();
    }

    public function availablePastExpiry(): Collection
    {
        return BloodBag::query()
            ->where('status', BagStatus::Available->value)
            ->where('expires_at', '<=', now())
            ->get();
    }

    public function markExpired(): int
    {
        return BloodBag::query()
            ->where('status', BagStatus::Available->value)
            ->where('expires_at', '<=', now())
            ->update(['status' => BagStatus::Expired->value]);
    }

    public function availableFor(array $groups, BloodComponent $component, int $limit = 40): Collection
    {
        return BloodBag::query()
            ->available()
            ->where('component', $component->value)
            ->whereIn('blood_group', array_map(fn ($g) => $g->value, $groups))
            ->orderBy('expires_at') // first expiry, first out
            ->limit($limit)
            ->get();
    }

    public function countAvailable(): int
    {
        return BloodBag::query()->available()->count();
    }
}
