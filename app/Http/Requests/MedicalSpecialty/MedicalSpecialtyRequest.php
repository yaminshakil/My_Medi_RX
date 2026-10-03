<?php

namespace App\Http\Requests\MedicalSpecialty;

use Illuminate\Foundation\Http\FormRequest;

class MedicalSpecialtyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:medical_specialties,name,'.($this->medical_specialty->id ?? 'NULL'),
            'icon' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:medical_specialties,id',
            'is_surgical' => 'boolean',
        ];
    }
}
