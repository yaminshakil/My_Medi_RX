<?php

namespace App\Http\Controllers\Api\V1\Pharmacy;

use App\Exceptions\PharmacyOperationException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StorePharmacyInventoryItemRequest;
use App\Services\ApiResponseService;
use App\Services\Pharmacy\PharmacyOperationsService;
use Illuminate\Http\JsonResponse;

class PharmacyInventoryController extends Controller
{
    public function __construct(private PharmacyOperationsService $operations) {}

    public function index(StorePharmacyInventoryItemRequest $request): JsonResponse
    {
        try {
            $items = $this->operations->inventory($request->user());
        } catch (PharmacyOperationException $exception) {
            return ApiResponseService::error($exception->getMessage(), [], $exception->statusCode);
        }

        return ApiResponseService::success($items->map(fn ($item): array => [
            'id'             => $item->id,
            'medicine_name'  => $item->medicine_name,
            'generic_name'   => $item->generic_name,
            'strength'       => $item->strength,
            'stock_quantity' => $item->stock_quantity,
            'reorder_level'  => $item->reorder_level,
            'expiry_date'    => $item->expiry_date?->toDateString(),
        ]));
    }

    public function store(StorePharmacyInventoryItemRequest $request): JsonResponse
    {
        try {
            $item = $this->operations->addInventoryItem(
                $request->user(),
                $request->validated(),
            );
        } catch (PharmacyOperationException $exception) {
            return ApiResponseService::error($exception->getMessage(), [], $exception->statusCode);
        }

        return ApiResponseService::success([
            'id'             => $item->id,
            'medicine_name'  => $item->medicine_name,
            'generic_name'   => $item->generic_name,
            'strength'       => $item->strength,
            'stock_quantity' => $item->stock_quantity,
            'reorder_level'  => $item->reorder_level,
            'expiry_date'    => $item->expiry_date?->toDateString(),
        ], 'Pharmacy inventory item created successfully.', 201);
    }

    public function update(StorePharmacyInventoryItemRequest $request, int $inventoryItem): JsonResponse
    {
        try {
            $item = $this->operations->updateInventoryItem(
                $request->user(),
                $inventoryItem,
                $request->validated(),
            );
        } catch (PharmacyOperationException $exception) {
            return ApiResponseService::error($exception->getMessage(), [], $exception->statusCode);
        }

        return ApiResponseService::success([
            'id'             => $item->id,
            'medicine_name'  => $item->medicine_name,
            'generic_name'   => $item->generic_name,
            'strength'       => $item->strength,
            'stock_quantity' => $item->stock_quantity,
            'reorder_level'  => $item->reorder_level,
            'expiry_date'    => $item->expiry_date?->toDateString(),
        ], 'Pharmacy inventory item updated successfully.');
    }
}
