<?php

namespace App\Http\Requests\Eprescription;

use Illuminate\Foundation\Http\FormRequest;

class CreateDoctorChamberRequest extends FormRequest
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
            'name' => ['required', 'string'],
            'city' => ['required', 'string'],
            'address' => ['required', 'string'],
            'header_left' => ['required', 'string', 'max:65000'],
            'header_right' => ['required', 'string', 'max:65000'],
            'footer_info' => ['required', 'string', 'max:65000'],
            'schedules' => 'required|array|min:1',
            'schedules.*.day' => 'required|string',
            'schedules.*.start_time' => 'required|date_format:H:i',
            'schedules.*.end_time' => 'required|date_format:H:i|after:schedules.*.start_time',
            'schedules.*.slot_duration' => 'required|numeric',
            'fee' => ['required', 'between:0,99.99'],
            'followup_fee' => ['required', 'between:0,99.99'],
            'report_fee' => ['nullable', 'between:0,99.99'],
            'appoinment_limit' => 'nullable|numeric',
            'chamber_logo' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:2048',
        ];
    }
}
