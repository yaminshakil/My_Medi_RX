<?php

namespace App\Http\Requests\Settings;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSeoSettingRequest extends FormRequest
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
            'seo_title'               => 'nullable|string|max:255',
            'seo_description'         => 'nullable|string|max:500',
            'seo_keywords'            => 'nullable|string|max:500',
            'seo_og_title'            => 'nullable|string|max:255',
            'seo_og_description'      => 'nullable|string|max:500',
            'seo_og_image_path'       => 'nullable|string',
            'seo_twitter_title'       => 'nullable|string|max:255',
            'seo_twitter_description' => 'nullable|string|max:500',
            'seo_twitter_image_path'  => 'nullable|string',
        ];
    }
}
