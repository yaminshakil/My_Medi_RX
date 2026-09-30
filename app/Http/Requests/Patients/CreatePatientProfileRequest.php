<?php

namespace App\Http\Requests\Patients;

use App\Models\Patient;
use Illuminate\Foundation\Http\FormRequest;

class CreatePatientProfileRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'date_of_birth' => 'required|date',
            'gender' => 'required|in:male,female,other',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email',
            'blood_group' => 'nullable|string',
            'city' => 'nullable|string',
            'address' => 'nullable|string',
            'marital_status' => 'nullable|string',
            'relationship_id' => 'nullable|numeric',
            'profile_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $userId = auth()->id();

            if (Patient::withTrashed()->where('user_id', $userId)->exists()) {
                $validator->errors()->add('user_id', 'You already have a profile (even if deleted). Contact admin to restore it.');
            }
        });
    }
}
