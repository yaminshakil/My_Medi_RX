<?php

namespace App\Http\Requests\Eprescription;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMedicineRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'brand_name' => 'required|string|max:500',
            'generic_name' => 'required|string|max:500',
            'strength' => 'required|string|max:500',
            'type' => 'required|string|max:50',
            'manufacturer_id' => 'required|exists:manufacturers,id',
            'is_active' => 'nullable|integer',
        ];
    }
}
