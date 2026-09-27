<?php

namespace App\Http\Requests\Settings;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateColorSettingRequest extends FormRequest
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
            'site_base_color'            => 'required|string',
            'primary_menu_bgcolor'       => 'required|string',
            'site_button_bgcolor'        => 'required|string',
            'site_button_hover_bgcolor'  => 'required|string',
            'site_header_top_bgcolor'    => 'required|string',
            'site_footer_bgcolor'        => 'required|string',
            'site_footer_bottom_bgcolor' => 'required|string',
        ];
    }
}
