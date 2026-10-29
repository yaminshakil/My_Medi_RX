<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StorePharmacyLicenseRequest extends FormRequest
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
        return [
            'license_number' => ['required', 'string', 'max:255'],
            'license_type'   => ['required', 'string', 'max:255'],
            'issued_date'    => ['nullable', 'date'],
            'expiry_date'    => ['nullable', 'date', 'after_or_equal:issued_date'],
            'document'       => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
        ];
    }
}
