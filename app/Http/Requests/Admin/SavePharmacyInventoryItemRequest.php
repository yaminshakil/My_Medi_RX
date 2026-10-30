<?php

namespace App\Http\Requests\Admin;

use App\Models\PharmacyInventoryItem;
use Illuminate\Foundation\Http\FormRequest;

class SavePharmacyInventoryItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        if ($user?->hasRole('Admin')) {
            return true;
        }

        if (! $user?->hasRole('Pharmacy') || ! $user->pharmacy) {
            return false;
        }

        $inventoryItem = $this->route('inventoryItem');

        return ! ($inventoryItem instanceof PharmacyInventoryItem)
            || $inventoryItem->pharmacy_id === $user->pharmacy->id;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $isCreating = $this->isMethod('POST');
        $isAdmin = $this->user()?->hasRole('Admin') ?? false;

        return [
            'pharmacy_id' => $isCreating && $isAdmin
                ? ['required', 'integer', 'exists:pharmacies,id']
                : ['prohibited'],
            'medicine_name'  => ['required', 'string', 'max:255'],
            'generic_name'   => ['nullable', 'string', 'max:255'],
            'strength'       => ['nullable', 'string', 'max:100'],
            'stock_quantity' => $isCreating
                ? ['required', 'integer', 'min:0', 'max:10000000']
                : ['prohibited'],
            'reorder_level' => ['required', 'integer', 'min:0', 'max:10000000'],
            'expiry_date'   => ['nullable', 'date'],
        ];
    }
}
