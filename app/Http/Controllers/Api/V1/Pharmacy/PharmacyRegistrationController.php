<?php

namespace App\Http\Controllers\Api\V1\Pharmacy;

use App\Exceptions\PharmacyConflictException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StorePharmacyRequest;
use App\Services\ApiResponseService;
use App\Services\Pharmacy\PharmacyService;
use Illuminate\Http\JsonResponse;

class PharmacyRegistrationController extends Controller
{
    public function __construct(private PharmacyService $pharmacyService) {}

    public function store(StorePharmacyRequest $request): JsonResponse
    {
        try {
            $pharmacy = $this->pharmacyService->register(
                $request->user(),
                $request->validated(),
            );
        } catch (PharmacyConflictException $exception) {
            return ApiResponseService::error($exception->getMessage(), [], 409);
        }

        return ApiResponseService::success([
            'id'          => $pharmacy->id,
            'name'        => $pharmacy->name,
            'slug'        => $pharmacy->slug,
            'phone'       => $pharmacy->phone,
            'email'       => $pharmacy->email,
            'description' => $pharmacy->description,
            'status'      => $pharmacy->status,
            'address'     => [
                'address_line_1' => $pharmacy->address->address_line_1,
                'address_line_2' => $pharmacy->address->address_line_2,
                'division'       => $pharmacy->address->division,
                'district'       => $pharmacy->address->district,
                'upazila'        => $pharmacy->address->upazila,
                'union'          => $pharmacy->address->union,
                'postal_code'    => $pharmacy->address->postal_code,
                'latitude'       => $pharmacy->address->latitude,
                'longitude'      => $pharmacy->address->longitude,
            ],
            'created_at' => $pharmacy->created_at?->toISOString(),
        ], 'Pharmacy registration submitted successfully.', 201);
    }
}
