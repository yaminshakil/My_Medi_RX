<?php

namespace App\Services;

use App\Models\PharmacyInventoryItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PharmacyInventoryAdminService
{
    public function adjustStock(
        PharmacyInventoryItem $inventoryItem,
        string $direction,
        int $quantity,
    ): PharmacyInventoryItem {
        return DB::transaction(function () use ($inventoryItem, $direction, $quantity): PharmacyInventoryItem {
            $lockedItem = PharmacyInventoryItem::query()
                ->lockForUpdate()
                ->findOrFail($inventoryItem->id);

            $adjustedQuantity = $direction === 'increase'
                ? $lockedItem->stock_quantity + $quantity
                : $lockedItem->stock_quantity - $quantity;

            if ($adjustedQuantity < 0) {
                throw ValidationException::withMessages([
                    'quantity' => 'The decrease quantity cannot exceed the available stock.',
                ]);
            }

            if ($adjustedQuantity > 10000000) {
                throw ValidationException::withMessages([
                    'quantity' => 'The adjusted stock cannot exceed 10,000,000 units.',
                ]);
            }

            $lockedItem->update(['stock_quantity' => $adjustedQuantity]);

            return $lockedItem->refresh();
        });
    }
}
