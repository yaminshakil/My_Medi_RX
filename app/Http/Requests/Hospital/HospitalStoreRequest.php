<?php

namespace App\Http\Requests\Hospital;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

class HospitalStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    // protected function failedValidation(Validator $validator)
    // {
    //     dd([
    //         'validation_errors' => $validator->errors()->toArray(),
    //         'request_data_sent' => $this->all(),
    //     ]);
    // }

    public function rules(): array
    {
        return [
            'hospital_name' => [
                'required',
                'string',
                'max:255',
            ],

            'hospital_type_id' => [
                'required',
                'exists:hospital_types,id',
            ],

            'email' => [
                'nullable',
                'email',
                'max:255',
            ],

            'mobile_number' => [
                'required',
                'string',
                'max:30',
            ],

            'emergency_contact' => [
                'nullable',
                'string',
                'max:30',
            ],

            'phone_number' => [
                'nullable',
                'string',
                'max:30',
            ],

            'contact_person_name' => [
                'nullable',
                'string',
                'max:255',
            ],

            'contact_person_mobile' => [
                'nullable',
                'string',
                'max:30',
            ],

            'address' => [
                'required',
                'string',
                'max:500',
            ],

            'registration_no' => [
                'nullable',
                'string',
                'max:255',
            ],

            'service_time' => [
                'nullable',
                'string',
                'max:255',
            ],

            'organization_notice' => [
                'nullable',
                'string',
                'max:255',
            ],

            'latitude' => [
                'nullable',
                'numeric',
                'between:-90,90',
            ],

            'longitude' => [
                'nullable',
                'numeric',
                'between:-180,180',
            ],

            'thana_id' => [
                'nullable',
                'exists:thanas,id',
            ],

            'district_id' => [
                'nullable',
                'exists:districts,id',
            ],

            'division_id' => [
                'nullable',
                'exists:divisions,id',
            ],

            'hospital_logo' => [
                'nullable',
                'image',
                'max:2048',
            ],

            'banner_url' => [
                'nullable',
                'image',
                'max:5120',
            ],

            'hospital_url' => [
                'nullable',
                'url',
                'max:255',
            ],

            'hospital_description' => [
                'nullable',
                'string',
            ],

            'sort_order' => [
                'nullable',
                'integer',
            ],
        ];
    }
}
