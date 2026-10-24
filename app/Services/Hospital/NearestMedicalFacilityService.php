<?php

namespace App\Services\Hospital;

use App\Models\Hospital;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class NearestMedicalFacilityService
{
    public function search(
        float $latitude,
        float $longitude,
        float $radius = 5,
        ?string $query = null,
    ): Collection {
        // Haversine formula: distance in kilometers.
        $distanceSql = '
            (
                6371 * ACOS(
                    LEAST(
                        1,
                        GREATEST(
                            -1,
                            COS(RADIANS(?))
                            * COS(RADIANS(latitude))
                            * COS(RADIANS(longitude) - RADIANS(?))
                            + SIN(RADIANS(?))
                            * SIN(RADIANS(latitude))
                        )
                    )
                )
            )
        ';

        return Hospital::query()
            ->with('hospitalType')
            ->where('status', 1)
            ->whereNull('deleted_at')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->whereBetween('latitude', [-90, 90])
            ->whereBetween('longitude', [-180, 180])
            ->when(
                $query !== null && trim($query) !== '',
                function (Builder $builder) use ($query) {
                    $term = '%' . trim($query) . '%';

                    $builder->where(function (Builder $search) use ($term) {
                        $search
                            ->where('hospital_name', 'like', $term)
                            ->orWhere('address', 'like', $term)
                            ->orWhere('registration_no', 'like', $term)
                            ->orWhere('phone_number', 'like', $term)
                            ->orWhere('mobile_number', 'like', $term)
                            ->orWhereHas(
                                'hospitalType',
                                function (Builder $typeQuery) use ($term) {
                                    // Change "name" if your hospital_types
                                    // table uses a different label column.
                                    $typeQuery->where('name', 'like', $term);
                                }
                            );
                    });
                }
            )
            ->select('hospitals.*')
            ->selectRaw(
                "{$distanceSql} AS distance_km",
                [$latitude, $longitude, $latitude]
            )
            ->having('distance_km', '<=', $radius)
            ->orderBy('distance_km')
            ->get();
    }
}
