<?php

namespace App\Http\Requests\Admin;

use App\Models\PharmacyInventoryItem;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdjustPharmacyInventoryStockRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        $inventoryItem = $this->route('inventoryItem');

        return $user?->hasRole('Admin')
            || ($user?->hasRole('Pharmacy')
                && $inventoryItem instanceof PharmacyInventoryItem
                && $inventoryItem->pharmacy_id === $user->pharmacy?->id);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'direction' => ['required', Rule::in(['increase', 'decrease'])],
            'quantity'  => ['required', 'integer', 'min:1', 'max:10000000'],
        ];
    }
}
