<?php

namespace App\Modules\BloodBank\Repositories\Contracts;

use App\Modules\BloodBank\Enums\BloodComponent;
use App\Modules\BloodBank\Models\BloodBag;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface BloodBagRepositoryInterface
{
    /** Server-side filtered + sorted list for the blood units page. */
    public function paginate(array $filters, int $perPage = 20): LengthAwarePaginator;

    public function create(array $data): BloodBag;

    public function update(BloodBag $bag, array $data): BloodBag;

    /** Row-locks an available bag inside a transaction so two staff cannot issue the same unit. */
    public function lockAvailable(int $id): ?BloodBag;

    /** Count / volume of usable units grouped by blood group + component. */
    public function stockMatrix(): Collection;

    /** Usable units inside their per-component alert window, soonest first. @param array<string,int> $windowsByComponent */
    public function expiringSoon(array $windowsByComponent, int $limit = 50): Collection;

    /** Units still flagged available whose expiry time has already passed. */
    public function availablePastExpiry(): Collection;

    /** Flips available-but-past-expiry units to "expired". Returns affected rows. */
    public function markExpired(): int;

    /** First-expiry-first-out list of usable units. @param \App\Modules\BloodBank\Enums\BloodGroup[] $groups */
    public function availableFor(array $groups, BloodComponent $component, int $limit = 40): Collection;

    public function countAvailable(): int;
}
