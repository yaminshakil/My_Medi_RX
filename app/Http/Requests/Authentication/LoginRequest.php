<?php

namespace App\Http\Requests\Authentication;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LoginRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'email' => [
                Rule::requiredIf(function () {
                    return ! in_array($this->login_type, ['google', 'facebook', 'apple']);
                }),
                'nullable',
                'email',
            ],
            'login_type' => 'nullable|in:google,facebook,apple,email',

            // Password is REQUIRED only if it's NOT a social login
            'password' => [
                Rule::requiredIf(function () {
                    return ! in_array($this->login_type, ['google', 'facebook', 'apple']);
                }),
                'nullable',
                'min:8',
            ],

            // Token is REQUIRED only if it IS a social login
            'token' => [
                Rule::requiredIf(function () {
                    return in_array($this->login_type, ['google', 'facebook', 'apple']);
                }),
                'nullable',
            ],
        ];
    }
}
