<?php

namespace App\Http\Requests\Eprescription;

use Illuminate\Foundation\Http\FormRequest;

class CreateAppointmentRequest extends FormRequest
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
            'doctor_id' => ['required', 'numeric'],
            'patient_id' => ['required', 'numeric'],
            'chamber_id' => ['required', 'numeric'],
            'appointment_date' => ['required', 'date'],
            'appointment_time' => ['nullable', 'date_format:H:i:s'],
            'status' => ['nullable', 'in:pending,confirmed,completed,cancelled'],
            'consultation_type' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            'fee' => ['nullable', 'between:0,99.99'],
            'is_paid' => ['nullable', 'boolean'],
            'appointment_type' => ['nullable', 'string'],
        ];
    }
}
