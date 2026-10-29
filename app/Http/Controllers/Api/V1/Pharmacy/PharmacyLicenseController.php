<?php

namespace App\Http\Controllers\Api\V1\Pharmacy;

use App\Exceptions\PharmacyConflictException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StorePharmacyLicenseRequest;
use App\Services\ApiResponseService;
use App\Services\Pharmacy\PharmacyService;
use Illuminate\Http\JsonResponse;

class PharmacyLicenseController extends Controller
{
    public function __construct(private PharmacyService $pharmacyService) {}

    public function store(StorePharmacyLicenseRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $document = $validated['document'];
        unset($validated['document']);

        try {
            $license = $this->pharmacyService->uploadLicenseDocument(
                $request->user(),
                $validated,
                $document,
            );
        } catch (PharmacyConflictException $exception) {
            return ApiResponseService::error($exception->getMessage(), [], 409);
        }

        return ApiResponseService::success([
            'id'             => $license->id,
            'license_number' => $license->license_number,
            'license_type'   => $license->license_type,
            'issued_date'    => $license->issued_date?->toDateString(),
            'expiry_date'    => $license->expiry_date?->toDateString(),
            'status'         => $license->status,
            'created_at'     => $license->created_at?->toISOString(),
        ], 'Pharmacy license document uploaded successfully.', 201);
    }
}
