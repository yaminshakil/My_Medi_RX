<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class DispensePharmacyPrescriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('Pharmacy', 'api') ?? false;
    }

    public function rules(): array
    {
        return [
            'items'                     => ['required', 'array', 'min:1'],
            'items.*'                   => ['required', 'array:inventory_item_id,quantity'],
            'items.*.inventory_item_id' => ['required', 'integer', 'distinct'],
            'items.*.quantity'          => ['required', 'integer', 'min:1', 'max:10000000'],
        ];
    }
}
