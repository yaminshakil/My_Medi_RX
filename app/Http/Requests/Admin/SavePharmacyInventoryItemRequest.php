<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class SavePharmacyInventoryItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('Admin') ?? false;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $isCreating = $this->isMethod('POST');

        return [
            'pharmacy_id' => $isCreating
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
