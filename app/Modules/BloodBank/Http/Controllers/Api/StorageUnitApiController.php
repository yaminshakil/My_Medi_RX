<?php

namespace App\Modules\BloodBank\Http\Controllers\Api;

use App\Modules\BloodBank\Models\StorageUnit;
use App\Modules\BloodBank\Repositories\Contracts\StorageUnitRepositoryInterface;
use Illuminate\Http\JsonResponse;

class StorageUnitApiController extends BloodBankApiController
{
    public function __construct(private readonly StorageUnitRepositoryInterface $storageUnits) {}

    public function index(): JsonResponse
    {
        return $this->success(
            $this->storageUnits->all()->map(fn (StorageUnit $unit): array => [
                'id'         => $unit->id,
                'name'       => $unit->name,
                'location'   => $unit->location,
                'type'       => $unit->type,
                'min_temp_c' => $unit->min_temp_c,
                'max_temp_c' => $unit->max_temp_c,
                'capacity'   => $unit->capacity,
                'is_active'  => $unit->is_active,
            ])->values(),
            'Storage units loaded.',
        );
    }
}
