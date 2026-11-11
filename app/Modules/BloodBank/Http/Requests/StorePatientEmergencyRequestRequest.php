<?php

namespace App\Modules\BloodBank\Http\Requests;

use App\Models\Hospital;
use App\Modules\BloodBank\Enums\BloodComponent;
use App\Modules\BloodBank\Enums\BloodGroup;
use App\Modules\BloodBank\Enums\UrgencyLevel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StorePatientEmergencyRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('Patient') ?? false;
    }

    public function rules(): array
    {
        return [
            'hospital_id' => [
                'required',
                'integer',
                Rule::exists('hospitals', 'id')->where('verification_status', 'approved'),
            ],
            'blood_group'  => ['required', Rule::in(BloodGroup::values())],
            'component'    => ['required', Rule::in(BloodComponent::values())],
            'units_needed' => ['required', 'integer', 'min:1', 'max:50'],
            'urgency'      => ['required', Rule::in(array_column(UrgencyLevel::cases(), 'value'))],
            'radius_km'    => ['nullable', 'numeric', 'min:1', 'max:'.config('blood-bank.emergency.max_radius_km')],
            'note'         => ['nullable', 'string', 'max:500'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->has('hospital_id')) {
                    return;
                }

                $hospital = Hospital::query()
                    ->where('verification_status', 'approved')
                    ->find($this->integer('hospital_id'));

                if (! $hospital || ! $hospital->bloodBankStorageUnits()->where('is_active', true)->exists()) {
                    $validator->errors()->add('hospital_id', 'Select a registered blood bank.');

                    return;
                }

                if (is_null($hospital->latitude) || is_null($hospital->longitude)) {
                    $validator->errors()->add('hospital_id', 'The selected blood bank needs a location before donor requests can be sent.');
                }
            },
        ];
    }
}
