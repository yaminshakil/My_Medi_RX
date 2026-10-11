<?php

namespace App\Modules\BloodBank\Http\Requests;

use App\Modules\BloodBank\Enums\BloodComponent;
use App\Modules\BloodBank\Enums\BloodGroup;
use App\Modules\BloodBank\Enums\UrgencyLevel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class StoreEmergencyRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('manage-blood-bank');
    }

    public function rules(): array
    {
        return [
            'blood_group'  => ['required', Rule::in(BloodGroup::values())],
            'component'    => ['required', Rule::in(BloodComponent::values())],
            'units_needed' => ['required', 'integer', 'min:1', 'max:50'],
            'urgency'      => ['required', Rule::in(array_column(UrgencyLevel::cases(), 'value'))],
            'radius_km'    => ['nullable', 'numeric', 'min:1', 'max:'.config('blood-bank.emergency.max_radius_km')],
            'note'         => ['nullable', 'string', 'max:500'],
        ];
    }
}
