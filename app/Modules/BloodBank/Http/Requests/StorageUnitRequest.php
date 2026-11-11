<?php

namespace App\Modules\BloodBank\Http\Requests;

use App\Modules\BloodBank\Enums\StorageUnitType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class StorageUnitRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        if (! $user || ! Gate::allows('manage-blood-bank')) {
            return false;
        }

        return $user->is_admin
            || ! $this->has('hospital_id')
            || (int) $this->input('hospital_id') === (int) $user->hospital?->id;
    }

    public function rules(): array
    {
        return [
            'name'       => ['required', 'string', 'max:255'],
            'location'   => ['nullable', 'string', 'max:255'],
            'type'       => ['required', Rule::in(StorageUnitType::values())],
            'min_temp_c' => ['nullable', 'numeric', 'between:-999.9,999.9', 'decimal:0,1'],
            'max_temp_c' => [
                'nullable',
                'numeric',
                'between:-999.9,999.9',
                'decimal:0,1',
                Rule::when($this->filled('min_temp_c'), ['gte:min_temp_c']),
            ],
            'capacity'    => ['nullable', 'integer', 'min:1', 'max:65535'],
            'is_active'   => ['required', 'boolean'],
            'hospital_id' => ['required', 'integer', Rule::exists('hospitals', 'id')],
        ];
    }
}
