<?php

namespace App\Http\Requests\Hospital;

use Illuminate\Foundation\Http\FormRequest;

class DoctorShiftRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage doctor shifts') ?? false;
    }

    public function rules(): array
    {
        return [
            'hospital_id' => ['required', 'integer', 'exists:hospitals,id'],
            'name'        => ['required', 'string', 'max:100'],
            'start_time'  => ['required', 'date_format:H:i'],
            'end_time'    => ['required', 'date_format:H:i', 'different:start_time'],
            'break_start' => ['nullable', 'date_format:H:i'],
            'break_end'   => ['nullable', 'date_format:H:i', 'after:break_start'],
            'is_active'   => ['required', 'boolean'],
        ];
    }
}
