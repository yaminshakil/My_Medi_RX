<?php

namespace App\Modules\BloodBank\Repositories\Eloquent;

use App\Modules\BloodBank\Models\BloodDonor;
use App\Modules\BloodBank\Repositories\Contracts\DonorRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class DonorRepository implements DonorRepositoryInterface
{
    public function paginate(array $filters, int $perPage = 20): LengthAwarePaginator
    {
        return BloodDonor::query()
            ->when($filters['search'] ?? null, fn ($q, $s) => $q->where(
                fn ($w) => $w->where('name', 'like', "%{$s}%")->orWhere('phone', 'like', "%{$s}%")->orWhere('email', 'like', "%{$s}%")
            ))
            ->when($filters['blood_group'] ?? null, fn ($q, $v) => $q->where('blood_group', $v))
            ->when(($filters['verified'] ?? '') !== '', fn ($q) => $q->where('is_verified', (bool) ($filters['verified'] ?? false)))
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function create(array $data): BloodDonor
    {
        return BloodDonor::create($data);
    }

    public function setVerified(BloodDonor $donor, bool $verified): BloodDonor
    {
        $donor->update([
            'is_verified' => $verified,
            'verified_at' => $verified ? now() : null,
        ]);

        return $donor;
    }

    public function findEligibleNearby(float $lat, float $lng, float $radiusKm, array $groups, int $limit): Collection
    {
        // Bounding box first: a cheap range check the lat/lng index can serve before any trigonometry runs.
        $latDelta = $radiusKm / 111.0;
        $lngDelta = $radiusKm / (111.0 * max(cos(deg2rad($lat)), 0.01));

        // Haversine-style great-circle distance in km (MySQL / MariaDB).
        $distance = '(6371 * ACOS(LEAST(1, COS(RADIANS(?)) * COS(RADIANS(latitude)) * COS(RADIANS(longitude) - RADIANS(?)) + SIN(RADIANS(?)) * SIN(RADIANS(latitude)))))';

        return BloodDonor::query()
            ->select('bb_donors.*')
            ->selectRaw("{$distance} AS distance_km", [$lat, $lng, $lat])
            ->where('is_verified', true)
            ->where('is_available', true)
            ->whereIn('blood_group', array_map(fn ($g) => $g->value, $groups))
            ->whereBetween('latitude', [$lat - $latDelta, $lat + $latDelta])
            ->whereBetween('longitude', [$lng - $lngDelta, $lng + $lngDelta])
            // Skip donors who gave too recently (default 56 days).
            ->where(fn ($q) => $q
                ->whereNull('last_donated_at')
                ->orWhere('last_donated_at', '<=', now()->subDays(config('blood-bank.donor_min_interval_days'))->toDateString()))
            ->having('distance_km', '<=', $radiusKm)
            ->orderBy('distance_km')
            ->limit($limit)
            ->get();
    }
}
