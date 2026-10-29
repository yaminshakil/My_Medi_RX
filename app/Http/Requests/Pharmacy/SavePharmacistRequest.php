<?php

namespace App\Http\Requests\Pharmacy;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SavePharmacistRequest extends FormRequest
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
            'name'                  => ['required', 'string', 'max:255'],
            'registration_number'   => ['nullable', 'string', 'max:255'],
            'phone'                 => ['nullable', 'string', 'max:30'],
            'email'                 => ['nullable', 'email', 'max:255'],
            'qualification'         => ['nullable', 'string', 'max:255'],
            'registration_document' => [
                Rule::requiredIf($this->route('pharmacist') === null),
                'nullable',
                'file',
                'mimes:pdf,jpg,jpeg,png',
                'max:10240',
            ],
            'is_primary' => ['nullable', 'boolean'],
        ];
    }
}
