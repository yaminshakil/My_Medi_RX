<?php

namespace App\Http\Requests\Hospital;

use Illuminate\Foundation\Http\FormRequest;

class DoctorRosterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage doctor rosters') ?? false;
    }

    public function rules(): array
    {
        return [
            'hospital_id' => ['required', 'integer', 'exists:hospitals,id'],
            'department_id' => ['required', 'integer', 'exists:departments,id'],
            'doctor_id' => ['required', 'integer', 'exists:users,id'],
            'shift_id' => ['nullable', 'integer', 'exists:doctor_shifts,id'],
            'roster_date' => ['required', 'date'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'different:start_time'],
            'room_label' => ['nullable', 'string', 'max:100'],
            'status' => ['required', 'in:scheduled,completed,cancelled'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
