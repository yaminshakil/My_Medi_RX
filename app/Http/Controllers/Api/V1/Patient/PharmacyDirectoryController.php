<?php

namespace App\Http\Controllers\Api\V1\Patient;

use App\Http\Controllers\Controller;
use App\Models\Pharmacy;
use App\Services\ApiResponseService;
use App\Services\Pharmacy\PharmacyOperationsService;
use Illuminate\Http\JsonResponse;

class PharmacyDirectoryController extends Controller
{
    public function __construct(private PharmacyOperationsService $operations) {}

    public function index(): JsonResponse
    {
        $pharmacies = $this->operations->approvedPharmacies();

        return ApiResponseService::success([
            'pharmacies' => $pharmacies->getCollection()
                ->map(fn (Pharmacy $pharmacy): array => [
                    'id'      => $pharmacy->id,
                    'name'    => $pharmacy->name,
                    'phone'   => $pharmacy->phone,
                    'email'   => $pharmacy->email,
                    'address' => $pharmacy->address === null ? null : [
                        'address_line_1' => $pharmacy->address->address_line_1,
                        'address_line_2' => $pharmacy->address->address_line_2,
                        'division'       => $pharmacy->address->division,
                        'district'       => $pharmacy->address->district,
                        'upazila'        => $pharmacy->address->upazila,
                        'postal_code'    => $pharmacy->address->postal_code,
                        'latitude'       => $pharmacy->address->latitude,
                        'longitude'      => $pharmacy->address->longitude,
                    ],
                ])
                ->all(),
            'pagination' => [
                'current_page' => $pharmacies->currentPage(),
                'last_page'    => $pharmacies->lastPage(),
                'per_page'     => $pharmacies->perPage(),
                'total'        => $pharmacies->total(),
            ],
        ]);
    }
}
