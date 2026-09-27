<?php

namespace App\Http\Requests\Settings;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateGeneralSettingRequest extends FormRequest
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
            'site_title'        => 'required|string',
            'currency'          => 'required|string|max:10',
            'currency_symbol'   => 'required|string|max:5',
            'timezone'          => 'required|string',
            'site_country_code' => 'required|string|max:5',
            'records_per_page'  => 'required|integer|min:1',
            'currency_format'   => 'required|string',
            'site_mobile'       => 'required|string',
            'site_email'        => 'required|email',
            'site_address'      => 'required|string',
        ];
    }
}
