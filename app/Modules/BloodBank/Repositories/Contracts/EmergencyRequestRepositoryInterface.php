<?php

namespace App\Modules\BloodBank\Repositories\Contracts;

use App\Modules\BloodBank\Models\EmergencyRequest;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface EmergencyRequestRepositoryInterface
{
    public function paginate(array $filters, int $perPage = 15): LengthAwarePaginator;

    public function create(array $data): EmergencyRequest;

    public function update(EmergencyRequest $request, array $data): EmergencyRequest;

    /** @param array<int, array<string, mixed>> $donorPivotData keyed by donor id */
    public function attachDonors(EmergencyRequest $request, array $donorPivotData): void;

    /** Returns true when the donor was on the request and the reply was stored. */
    public function recordResponse(EmergencyRequest $request, int $donorId, string $response): bool;

    public function countOpen(): int;

    /** Open requests past their expiry time become "expired". Returns affected rows. */
    public function expireStale(): int;
}
