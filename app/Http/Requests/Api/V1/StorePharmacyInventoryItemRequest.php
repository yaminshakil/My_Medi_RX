<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StorePharmacyInventoryItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $required = $this->isMethod('POST') ? 'required' : 'sometimes';

        return [
            'medicine_name'       => [$required, 'string', 'max:255'],
            'generic_name'        => ['sometimes', 'nullable', 'string', 'max:255'],
            'strength'            => ['sometimes', 'nullable', 'string', 'max:100'],
            'regular_price'       => ['sometimes', 'numeric', 'min:0', 'max:99999999.99', 'decimal:0,2'],
            'discount_percentage' => ['sometimes', 'numeric', 'min:0', 'max:100', 'decimal:0,2'],
            'stock_quantity'      => [$required, 'integer', 'min:0', 'max:10000000'],
            'reorder_level'       => [$required, 'integer', 'min:0', 'max:10000000'],
            'expiry_date'         => ['sometimes', 'nullable', 'date', 'after_or_equal:today'],
        ];
    }
}
