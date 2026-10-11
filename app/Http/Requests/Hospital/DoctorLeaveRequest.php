<?php

namespace App\Http\Requests\Hospital;

use Illuminate\Foundation\Http\FormRequest;

class DoctorLeaveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('request doctor leave') ?? false;
    }

    public function rules(): array
    {
        return [
            'hospital_id' => ['required', 'integer', 'exists:hospitals,id'],
            'doctor_id' => ['required', 'integer', 'exists:users,id'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'start_time' => ['nullable', 'date_format:H:i', 'required_with:end_time'],
            'end_time' => ['nullable', 'date_format:H:i', 'required_with:start_time', 'after:start_time'],
            'leave_type' => ['required', 'in:annual,sick,emergency,other'],
            'reason' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
