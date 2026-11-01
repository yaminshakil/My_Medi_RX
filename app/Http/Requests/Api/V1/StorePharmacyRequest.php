<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StorePharmacyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name'        => ['required', 'string', 'max:255'],
            'phone'       => ['nullable', 'string', 'max:30'],
            'email'       => ['nullable', 'email', 'max:255'],
            'description' => ['nullable', 'string', 'max:10000'],
            'address'     => [
                'required',
                'array:address_line_1,address_line_2,division,district,upazila,union,postal_code,latitude,longitude',
            ],
            'address.address_line_1' => ['required', 'string', 'max:255'],
            'address.address_line_2' => ['nullable', 'string', 'max:255'],
            'address.division'       => ['nullable', 'string', 'max:255'],
            'address.district'       => ['nullable', 'string', 'max:255'],
            'address.upazila'        => ['nullable', 'string', 'max:255'],
            'address.union'          => ['nullable', 'string', 'max:255'],
            'address.postal_code'    => ['nullable', 'string', 'max:20'],
            'address.latitude'       => ['nullable', 'numeric', 'between:-90,90'],
            'address.longitude'      => ['nullable', 'numeric', 'between:-180,180'],
        ];
    }
}
