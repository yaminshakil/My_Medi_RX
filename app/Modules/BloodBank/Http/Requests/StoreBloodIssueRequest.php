<?php

namespace App\Modules\BloodBank\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class StoreBloodIssueRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('manage-blood-bank');
    }

    public function rules(): array
    {
        return [
            'blood_bag_id'                  => ['required', 'integer', 'exists:bb_blood_bags,id'],
            'patient_id'                    => ['required', 'integer', 'exists:bb_patients,id'],
            'physician_id'                  => ['required', 'integer', 'exists:bb_physicians,id'],
            'indication'                    => ['required', 'string', 'max:1000'],
            'ward'                          => ['nullable', 'string', 'max:100'],
            'emergency_request_id'          => ['nullable', 'integer', 'exists:bb_emergency_requests,id'],
            'compatibility_override_reason' => ['nullable', 'string', 'max:500'],
        ];
    }
}
