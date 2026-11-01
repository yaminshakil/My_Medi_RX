<?php

namespace App\Modules\BloodBank\Http\Controllers\Api;

use App\Modules\BloodBank\Enums\BagStatus;
use App\Modules\BloodBank\Enums\BloodComponent;
use App\Modules\BloodBank\Enums\BloodGroup;
use App\Modules\BloodBank\Exceptions\BloodBankException;
use App\Modules\BloodBank\Http\Requests\StoreBloodBagRequest;
use App\Modules\BloodBank\Http\Resources\BloodBagResource;
use App\Modules\BloodBank\Models\BloodBag;
use App\Modules\BloodBank\Repositories\Contracts\BloodBagRepositoryInterface;
use App\Modules\BloodBank\Services\BloodBagService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BloodBagApiController extends BloodBankApiController
{
    public function __construct(
        private readonly BloodBagRepositoryInterface $bags,
        private readonly BloodBagService $service,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'search'          => ['nullable', 'string', 'max:100'],
            'blood_group'     => ['nullable', Rule::in(BloodGroup::values())],
            'component'       => ['nullable', Rule::in(BloodComponent::values())],
            'status'          => ['nullable', Rule::in(array_column(BagStatus::cases(), 'value'))],
            'expiry'          => ['nullable', Rule::in(['expired', 'expiring'])],
            'storage_unit_id' => ['nullable', 'integer', 'exists:bb_storage_units,id'],
            'sort'            => ['nullable', Rule::in(['expires_at', 'collected_at', 'bag_number', 'blood_group', 'created_at'])],
            'direction'       => ['nullable', Rule::in(['asc', 'desc'])],
            'per_page'        => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);
        $perPage = $filters['per_page'] ?? 20;
        unset($filters['per_page']);

        return $this->paginated(
            $this->bags->paginate($filters, $perPage),
            fn (BloodBag $bag): array => BloodBagResource::make($bag)->resolve($request),
        );
    }

    public function store(StoreBloodBagRequest $request): JsonResponse
    {
        $bag = $this->service->register($request->user(), $request->validated());
        $bag->load(['storageUnit:id,name', 'donor:id,name']);

        return $this->success(
            BloodBagResource::make($bag)->resolve($request),
            'Blood unit registered.',
            201,
        );
    }

    public function discard(Request $request, BloodBag $bag): JsonResponse
    {
        $data = $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        try {
            $this->service->discard($bag, $data['reason']);
        } catch (BloodBankException $exception) {
            return $this->conflict($exception);
        }

        $bag->load(['storageUnit:id,name', 'donor:id,name']);

        return $this->success(BloodBagResource::make($bag)->resolve($request), 'Blood unit discarded.');
    }

    public function release(Request $request, BloodBag $bag): JsonResponse
    {
        try {
            $this->service->release($bag);
        } catch (BloodBankException $exception) {
            return $this->conflict($exception);
        }

        $bag->load(['storageUnit:id,name', 'donor:id,name']);

        return $this->success(BloodBagResource::make($bag)->resolve($request), 'Blood unit released to stock.');
    }
}
