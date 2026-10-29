<?php

namespace App\Http\Requests\Pharmacy;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SavePharmacyRequest extends FormRequest
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
            'user_id'                => ['required', 'integer', Rule::exists('users', 'id')],
            'name'                   => ['required', 'string', 'max:255'],
            'phone'                  => ['nullable', 'string', 'max:30'],
            'email'                  => ['nullable', 'email', 'max:255'],
            'description'            => ['nullable', 'string', 'max:10000'],
            'logo'                   => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'address'                => ['nullable', 'array'],
            'address.address_line_1' => ['nullable', 'string', 'max:255'],
            'address.address_line_2' => ['nullable', 'string', 'max:255'],
            'address.division'       => ['nullable', 'string', 'max:255'],
            'address.district'       => ['nullable', 'string', 'max:255'],
            'address.upazila'        => ['nullable', 'string', 'max:255'],
            'address.union'          => ['nullable', 'string', 'max:255'],
            'address.postal_code'    => ['nullable', 'string', 'max:20'],
            'address.latitude'       => ['nullable', 'numeric', 'between:-90,90'],
            'address.longitude'      => ['nullable', 'numeric', 'between:-180,180'],
            'address.is_primary'     => ['nullable', 'boolean'],
        ];
    }
}
