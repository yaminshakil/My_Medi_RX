<?php

namespace App\Modules\BloodBank\Http\Controllers\Api;

use App\Modules\BloodBank\Enums\BloodGroup;
use App\Modules\BloodBank\Http\Requests\StoreDonorRequest;
use App\Modules\BloodBank\Http\Resources\BloodDonorResource;
use App\Modules\BloodBank\Models\BloodDonor;
use App\Modules\BloodBank\Repositories\Contracts\DonorRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DonorApiController extends BloodBankApiController
{
    public function __construct(private readonly DonorRepositoryInterface $donors) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'search'      => ['nullable', 'string', 'max:100'],
            'blood_group' => ['nullable', Rule::in(BloodGroup::values())],
            'verified'    => ['nullable', 'boolean'],
            'per_page'    => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);
        $perPage = $filters['per_page'] ?? 20;

        if (array_key_exists('verified', $filters)) {
            $filters['verified'] = filter_var($filters['verified'], FILTER_VALIDATE_BOOLEAN);
        }
        unset($filters['per_page']);

        return $this->paginated(
            $this->donors->paginate($filters, $perPage),
            fn (BloodDonor $donor): array => BloodDonorResource::make($donor)->resolve($request),
        );
    }

    public function store(StoreDonorRequest $request): JsonResponse
    {
        $donor = $this->donors->create($request->validated());

        return $this->success(BloodDonorResource::make($donor)->resolve($request), 'Donor registered.', 201);
    }

    public function verify(Request $request, BloodDonor $donor): JsonResponse
    {
        $data = $request->validate([
            'verified' => ['required', 'boolean'],
        ]);
        $donor = $this->donors->setVerified($donor, (bool) $data['verified']);

        return $this->success(
            BloodDonorResource::make($donor)->resolve($request),
            $data['verified'] ? 'Donor verified.' : 'Donor verification removed.',
        );
    }
}
