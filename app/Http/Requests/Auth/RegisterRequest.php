<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name' => [
                'required',
                'string',
                'max:255',
            ],

            'last_name' => [
                'required',
                'string',
                'max:255',
            ],

            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique('users', 'email'),
            ],

            'password' => [
                'required',
                'confirmed',
                Password::defaults(),
            ],

            'registration_type' => [
                'required',
                'string',
                Rule::in(['Doctor', 'Patient']),
            ],

            'mobile' => [
                'required',
                'string',
                'max:255',
                Rule::unique('users', 'mobile'),
            ],

            'bmdc_number' => [
                'nullable',
                'string',
                'max:255',
                Rule::requiredIf(
                    fn () => $this->registration_type === 'Doctor'
                ),
                Rule::unique('doctors', 'registration_no'),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'first_name.required' => 'First name is required.',
            'last_name.required' => 'Last name is required.',

            'email.required' => 'Email address is required.',
            'email.email' => 'Please provide a valid email address.',
            'email.unique' => 'This email address is already registered.',

            'password.required' => 'Password is required.',
            'password.confirmed' => 'Password confirmation does not match.',

            'registration_type.required' => 'Registration type is required.',
            'registration_type.in' => 'Registration type must be Doctor or Patient.',

            'mobile.required' => 'Mobile number is required.',
            'mobile.unique' => 'This mobile number is already registered. Please use another mobile number.',

            'bmdc_number.required' => 'BMDC registration number is required for doctors.',
            'bmdc_number.unique' => 'This BMDC registration number is already registered.',
        ];
    }

    public function attributes(): array
    {
        return [
            'first_name' => 'first name',
            'last_name' => 'last name',
            'registration_type' => 'registration type',
            'bmdc_number' => 'BMDC number',
        ];
    }
}
