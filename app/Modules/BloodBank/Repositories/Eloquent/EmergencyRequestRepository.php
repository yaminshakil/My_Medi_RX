<?php

namespace App\Modules\BloodBank\Repositories\Eloquent;

use App\Modules\BloodBank\Enums\EmergencyStatus;
use App\Modules\BloodBank\Models\EmergencyRequest;
use App\Modules\BloodBank\Repositories\Contracts\EmergencyRequestRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EmergencyRequestRepository implements EmergencyRequestRepositoryInterface
{
    public function paginate(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return EmergencyRequest::query()
            ->with('hospital:id,name')
            ->withCount(['donors as accepted_count' => fn ($q) => $q->where('bb_emergency_request_donor.response', 'accepted')])
            ->when($filters['status'] ?? null, fn ($q, $v) => $q->where('status', $v))
            ->latest('broadcast_at')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function create(array $data): EmergencyRequest
    {
        return EmergencyRequest::create($data);
    }

    public function update(EmergencyRequest $request, array $data): EmergencyRequest
    {
        $request->update($data);

        return $request;
    }

    public function attachDonors(EmergencyRequest $request, array $donorPivotData): void
    {
        if ($donorPivotData !== []) {
            $request->donors()->attach($donorPivotData);
        }
    }

    public function recordResponse(EmergencyRequest $request, int $donorId, string $response): bool
    {
        return $request->donors()->updateExistingPivot($donorId, [
            'response'     => $response,
            'responded_at' => now(),
        ]) > 0;
    }

    public function countOpen(): int
    {
        return EmergencyRequest::query()
            ->where('status', EmergencyStatus::Open->value)
            ->where('expires_at', '>', now())
            ->count();
    }

    public function expireStale(): int
    {
        return EmergencyRequest::query()
            ->where('status', EmergencyStatus::Open->value)
            ->where('expires_at', '<=', now())
            ->update(['status' => EmergencyStatus::Expired->value, 'closed_at' => now()]);
    }
}
