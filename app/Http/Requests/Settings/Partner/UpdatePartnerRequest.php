<?php

namespace App\Http\Requests\Settings\Partner;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePartnerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name'   => 'required|string|max:255',
            'logo'   => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'url'    => 'nullable|url',
            'active' => 'boolean',
        ];
    }
}
