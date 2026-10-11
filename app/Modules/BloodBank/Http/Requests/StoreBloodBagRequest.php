<?php

namespace App\Modules\BloodBank\Http\Requests;

use App\Modules\BloodBank\Enums\BloodComponent;
use App\Modules\BloodBank\Enums\BloodGroup;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class StoreBloodBagRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('manage-blood-bank');
    }

    public function rules(): array
    {
        return [
            'bag_number'      => ['required', 'string', 'max:60', 'unique:bb_blood_bags,bag_number'],
            'batch_number'    => ['required', 'string', 'max:60'],
            'donor_id'        => ['nullable', 'integer', 'exists:bb_donors,id'],
            'blood_group'     => ['required', Rule::in(BloodGroup::values())],
            'component'       => ['required', Rule::in(BloodComponent::values())],
            'volume_ml'       => ['required', 'integer', 'min:10', 'max:1000'],
            'collected_at'    => ['required', 'date', 'before_or_equal:now'],
            'expires_at'      => ['nullable', 'date', 'after:collected_at'],
            'storage_unit_id' => ['required', 'integer', 'exists:bb_storage_units,id'],
            'quarantine'      => ['boolean'],
        ];
    }
}
