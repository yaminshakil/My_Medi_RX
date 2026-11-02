<?php

namespace App\Modules\BloodBank\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class StorePhysicianRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('manage-blood-bank');
    }

    public function rules(): array
    {
        return [
            'name'       => ['required', 'string', 'max:255'],
            'license_no' => ['nullable', 'string', 'max:60', 'unique:bb_physicians,license_no'],
            'department' => ['nullable', 'string', 'max:120'],
            'phone'      => ['nullable', 'string', 'max:40'],
        ];
    }
}
