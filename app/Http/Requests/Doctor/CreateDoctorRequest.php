<?php

namespace App\Http\Requests\Doctor;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CreateDoctorRequest extends FormRequest
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
        $userId = $this->route('doctor')?->user_id; // for unique email on update

        return [
            'name'                 => 'required|string|max:255',
            'email'                => 'required|email|unique:users,email,'.$userId,
            'password'             => $this->isMethod('post') ? 'required|min:8|confirmed' : 'nullable|min:8|confirmed',
            'profile_image'        => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'phone'                => 'nullable|string|max:20',
            'gender'               => 'nullable|in:male,female,other',
            'dob'                  => 'nullable|date',
            'specialization'       => 'nullable|string|max:255',
            'working_institute'    => 'nullable|string|max:255',
            'registration_no'      => 'nullable|string|max:255',
            'designation'          => 'nullable|string|max:255',
            'qualification'        => 'nullable|string|max:255',
            'experience_years'     => 'nullable|integer|min:0',
            'bio'                  => 'nullable|string',
            'specialization_ids'   => ['required', 'array'], // must be an array if present
            'specialization_ids.*' => ['integer', 'exists:medical_specialties,id'], // each item must exist
            'social'               => ['nullable', 'array'],
            'social.facebook'      => ['nullable', 'url'],
            'social.linkedin'      => ['nullable', 'url'],
            'social.twitter'       => ['nullable', 'url'],
            'featured'             => ['boolean'],
            'active'               => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'specialization_ids.required'  => 'Please select at least one specialty.',
            'specialization_ids.array'     => 'Specialties must be an array.',
            'specialization_ids.*.integer' => 'Each specialty must be a valid ID.',
            'specialization_ids.*.exists'  => 'One or more selected specialties do not exist.',
        ];
    }
}
