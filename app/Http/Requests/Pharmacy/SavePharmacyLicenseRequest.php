<?php

namespace App\Http\Requests\Pharmacy;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SavePharmacyLicenseRequest extends FormRequest
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
            'license_number' => ['required', 'string', 'max:255'],
            'license_type'   => ['required', 'string', 'max:255'],
            'issued_date'    => ['nullable', 'date'],
            'expiry_date'    => ['nullable', 'date', 'after_or_equal:issued_date'],
            'document'       => [
                Rule::requiredIf($this->route('license') === null),
                'nullable',
                'file',
                'mimes:pdf,jpg,jpeg,png',
                'max:10240',
            ],
        ];
    }
}
