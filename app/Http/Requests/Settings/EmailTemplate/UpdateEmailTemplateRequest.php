<?php

namespace App\Http\Requests\Settings\EmailTemplate;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateEmailTemplateRequest extends FormRequest
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
        $templateId = $this->route('email_template');

        return [
            'name'    => 'required|string|max:255',
            'slug'    => 'required|string|max:255|unique:email_templates,slug,'.$templateId,
            'subject' => 'required|string',
            'body'    => 'required|string',
        ];
    }
}
