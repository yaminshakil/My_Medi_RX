<?php

namespace App\Http\Requests\Hospital;

use Illuminate\Foundation\Http\FormRequest;

class DoctorAvailabilityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage doctor availability') ?? false;
    }

    public function rules(): array
    {
        return [
            'hospital_id'   => ['required', 'integer', 'exists:hospitals,id'],
            'department_id' => ['required', 'integer', 'exists:departments,id'],
            'doctor_id'     => ['required', 'integer', 'exists:users,id'],
            'shift_id'      => ['nullable', 'integer', 'exists:doctor_shifts,id'],
            'day_of_week'   => ['required', 'integer', 'between:0,6'],
            'start_time'    => ['required', 'date_format:H:i'],
            'end_time'      => ['required', 'date_format:H:i', 'different:start_time'],
            'slot_duration' => ['required', 'integer', 'between:5,120'],
            'max_patients'  => ['nullable', 'integer', 'min:1', 'max:1000'],
            'room_label'    => ['nullable', 'string', 'max:100'],
            'is_available'  => ['required', 'boolean'],
        ];
    }
}
