<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdjustPharmacyInventoryStockRequest extends FormRequest
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
        return [
            'direction' => ['required', Rule::in(['increase', 'decrease'])],
            'quantity'  => ['required', 'integer', 'min:1', 'max:10000000'],
        ];
    }
}
