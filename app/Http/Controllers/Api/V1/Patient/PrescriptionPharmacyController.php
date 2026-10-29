<?php

namespace App\Http\Controllers\Api\V1\Patient;

use App\Exceptions\PharmacyOperationException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\SharePrescriptionWithPharmacyRequest;
use App\Models\Prescription;
use App\Services\ApiResponseService;
use App\Services\Pharmacy\PharmacyOperationsService;
use Illuminate\Http\JsonResponse;

class PrescriptionPharmacyController extends Controller
{
    public function __construct(private PharmacyOperationsService $operations) {}

    public function store(
        SharePrescriptionWithPharmacyRequest $request,
        Prescription $prescription,
    ): JsonResponse {
        try {
            $order = $this->operations->sharePrescriptionWithPharmacy(
                $request->user(),
                $prescription->uuid,
                (int) $request->validated('pharmacy_id'),
            );
        } catch (PharmacyOperationException $exception) {
            return ApiResponseService::error($exception->getMessage(), [], $exception->statusCode);
        }

        return ApiResponseService::success([
            'id'          => $order->id,
            'status'      => $order->status,
            'received_at' => $order->received_at?->toISOString(),
        ], 'Prescription shared with pharmacy.', 201);
    }
}
