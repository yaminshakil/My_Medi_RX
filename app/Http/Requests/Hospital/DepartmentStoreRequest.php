<?php

namespace App\Http\Requests\Hospital;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DepartmentStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $hospitalId = $this->input('hospital_id');

        return [
            'hospital_id' => [
                'required',
                'integer',
                'exists:hospitals,id',
            ],

            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'code' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('departments', 'code')
                    ->where(fn ($query) => $query->where(
                        'hospital_id',
                        $hospitalId
                    )),
            ],

            'description' => [
                'nullable',
                'string',
                'max:5000',
            ],

            'floor' => [
                'nullable',
                'string',
                'max:100',
            ],

            'phone_number' => [
                'nullable',
                'string',
                'max:50',
            ],

            'email' => [
                'nullable',
                'email',
                'max:255',
            ],

            'sort_order' => [
                'nullable',
                'integer',
                'min:0',
                'max:1000000',
            ],

            'status' => [
                'required',
                'boolean',
            ],
        ];
    }
}
