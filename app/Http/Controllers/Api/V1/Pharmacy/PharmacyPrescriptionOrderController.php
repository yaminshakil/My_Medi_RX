<?php

namespace App\Http\Controllers\Api\V1\Pharmacy;

use App\Exceptions\PharmacyOperationException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\DispensePharmacyPrescriptionRequest;
use App\Services\ApiResponseService;
use App\Services\Pharmacy\PharmacyOperationsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PharmacyPrescriptionOrderController extends Controller
{
    public function __construct(private PharmacyOperationsService $operations) {}

    public function index(Request $request): JsonResponse
    {
        try {
            $orders = $this->operations->prescriptionOrders($request->user());
        } catch (PharmacyOperationException $exception) {
            return ApiResponseService::error($exception->getMessage(), [], $exception->statusCode);
        }

        return ApiResponseService::success($orders->map(fn ($order): array => [
            'id'           => $order->id,
            'status'       => $order->status,
            'received_at'  => $order->received_at?->toISOString(),
            'dispensed_at' => $order->dispensed_at?->toISOString(),
            'prescription' => [
                'uuid'                => $order->prescription->uuid,
                'prescription_number' => $order->prescription->prescription_number,
                'issued_at'           => $order->prescription->created_at?->toISOString(),
                'medications'         => $order->prescription->medications->map(fn ($medication): array => [
                    'medicine' => $medication->medicine?->brand_name
                        ?? $medication->medicine?->generic_name,
                    'strength' => $medication->strength,
                    'dosage'   => $medication->dosage,
                    'duration' => $medication->duration,
                ])->all(),
            ],
        ]));
    }

    public function process(Request $request, int $order): JsonResponse
    {
        try {
            $prescriptionOrder = $this->operations->startPrescriptionProcessing(
                $request->user(),
                $order,
            );
        } catch (PharmacyOperationException $exception) {
            return ApiResponseService::error($exception->getMessage(), [], $exception->statusCode);
        }

        return ApiResponseService::success([
            'id'     => $prescriptionOrder->id,
            'status' => $prescriptionOrder->status,
        ], 'Prescription moved into processing.');
    }

    public function dispense(
        DispensePharmacyPrescriptionRequest $request,
        int $order,
    ): JsonResponse {
        try {
            $prescriptionOrder = $this->operations->dispensePrescription(
                $request->user(),
                $order,
                $request->validated('items'),
            );
        } catch (PharmacyOperationException $exception) {
            return ApiResponseService::error($exception->getMessage(), [], $exception->statusCode);
        }

        return ApiResponseService::success([
            'id'           => $prescriptionOrder->id,
            'status'       => $prescriptionOrder->status,
            'dispensed_at' => $prescriptionOrder->dispensed_at?->toISOString(),
        ], 'Prescription dispensed and inventory updated.');
    }
}
