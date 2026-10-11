<?php

namespace App\Modules\BloodBank\Repositories\Contracts;

use App\Modules\BloodBank\Models\BloodDonor;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface DonorRepositoryInterface
{
    public function paginate(array $filters, int $perPage = 20): LengthAwarePaginator;

    public function create(array $data): BloodDonor;

    public function setVerified(BloodDonor $donor, bool $verified): BloodDonor;

    /**
     * Verified, opted-in, rested donors inside $radiusKm of the point, nearest first.
     * Each row carries a computed `distance_km` attribute.
     *
     * @param \App\Modules\BloodBank\Enums\BloodGroup[] $groups
     */
    public function findEligibleNearby(float $lat, float $lng, float $radiusKm, array $groups, int $limit): Collection;
}
