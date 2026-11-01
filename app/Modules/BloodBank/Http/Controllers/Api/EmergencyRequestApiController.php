<?php

namespace App\Modules\BloodBank\Http\Controllers\Api;

use App\Modules\BloodBank\Enums\EmergencyStatus;
use App\Modules\BloodBank\Exceptions\BloodBankException;
use App\Modules\BloodBank\Http\Requests\StoreEmergencyRequestRequest;
use App\Modules\BloodBank\Http\Resources\EmergencyRequestResource;
use App\Modules\BloodBank\Models\EmergencyRequest;
use App\Modules\BloodBank\Repositories\Contracts\EmergencyRequestRepositoryInterface;
use App\Modules\BloodBank\Services\EmergencyBroadcastService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EmergencyRequestApiController extends BloodBankApiController
{
    public function __construct(
        private readonly EmergencyRequestRepositoryInterface $requests,
        private readonly EmergencyBroadcastService $service,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'status'   => ['nullable', Rule::in(array_column(EmergencyStatus::cases(), 'value'))],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);
        $perPage = $filters['per_page'] ?? 15;
        unset($filters['per_page']);

        return $this->paginated(
            $this->requests->paginate($filters, $perPage),
            fn (EmergencyRequest $emergencyRequest): array => EmergencyRequestResource::make($emergencyRequest)->resolve($request),
        );
    }

    public function store(StoreEmergencyRequestRequest $request): JsonResponse
    {
        try {
            $emergencyRequest = $this->service->broadcast($request->user(), $request->validated());
        } catch (BloodBankException $exception) {
            return $this->conflict($exception);
        }

        $emergencyRequest->load('hospital:id,hospital_name');

        $message = $emergencyRequest->donors_notified_count > 0
            ? "Emergency request broadcast to {$emergencyRequest->donors_notified_count} donor(s)."
            : 'Emergency request created, but no eligible donors were found.';

        return $this->success(
            EmergencyRequestResource::make($emergencyRequest)->resolve($request),
            $message,
            201,
        );
    }

    public function show(Request $request, EmergencyRequest $emergencyRequest): JsonResponse
    {
        $emergencyRequest->load(['hospital:id,hospital_name', 'donors']);
        $emergencyRequest->loadCount([
            'donors as accepted_count' => fn ($query) => $query->where('bb_emergency_request_donor.response', 'accepted'),
        ]);

        return $this->success([
            'request' => EmergencyRequestResource::make($emergencyRequest)->resolve($request),
            'donors'  => $emergencyRequest->donors
                ->sortBy(fn ($donor) => $donor->pivot->distance_km)
                ->map(fn ($donor) => [
                    'id'           => $donor->id,
                    'name'         => $donor->name,
                    'phone'        => $donor->phone,
                    'blood_group'  => $donor->blood_group->value,
                    'distance_km'  => (float) $donor->pivot->distance_km,
                    'response'     => $donor->pivot->response,
                    'responded_at' => $donor->pivot->responded_at,
                ])->values(),
        ], 'Emergency request loaded.');
    }

    public function fulfill(Request $request, EmergencyRequest $emergencyRequest): JsonResponse
    {
        return $this->close($request, $emergencyRequest, EmergencyStatus::Fulfilled);
    }

    public function cancel(Request $request, EmergencyRequest $emergencyRequest): JsonResponse
    {
        return $this->close($request, $emergencyRequest, EmergencyStatus::Cancelled);
    }

    private function close(Request $request, EmergencyRequest $emergencyRequest, EmergencyStatus $status): JsonResponse
    {
        try {
            $this->service->close($emergencyRequest, $status);
        } catch (BloodBankException $exception) {
            return $this->conflict($exception);
        }

        $emergencyRequest->load('hospital:id,hospital_name');

        return $this->success(
            EmergencyRequestResource::make($emergencyRequest)->resolve($request),
            $status === EmergencyStatus::Fulfilled ? 'Emergency request fulfilled.' : 'Emergency request cancelled.',
        );
    }
}
