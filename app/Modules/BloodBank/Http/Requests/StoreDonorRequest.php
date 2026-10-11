<?php

namespace App\Modules\BloodBank\Http\Requests;

use App\Modules\BloodBank\Enums\BloodGroup;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class StoreDonorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('manage-blood-bank');
    }

    public function rules(): array
    {
        return [
            'name'            => ['required', 'string', 'max:255'],
            'phone'           => ['nullable', 'string', 'max:40'],
            'email'           => ['nullable', 'email', 'max:255'],
            'blood_group'     => ['required', Rule::in(BloodGroup::values())],
            'latitude'        => ['required', 'numeric', 'between:-90,90'],
            'longitude'       => ['required', 'numeric', 'between:-180,180'],
            'city'            => ['nullable', 'string', 'max:120'],
            'last_donated_at' => ['nullable', 'date', 'before_or_equal:today'],
        ];
    }
}
