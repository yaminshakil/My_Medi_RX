<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ImportPharmacyInventoryCsvRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user?->hasRole('Admin')
            || ($user?->hasRole('Pharmacy') && $user->pharmacy !== null);
    }

    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'mimes:csv,txt', 'extensions:csv,txt', 'max:5120'],
        ];
    }
}
