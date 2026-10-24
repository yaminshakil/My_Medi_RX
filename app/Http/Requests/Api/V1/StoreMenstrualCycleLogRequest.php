<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreMenstrualCycleLogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'date'         => ['required', 'date_format:Y-m-d', 'before_or_equal:today'],
            'flow'         => ['required', 'in:None,Light,Medium,Heavy'],
            'cramps'       => ['required', 'in:None,Mild,Moderate,Severe'],
            'mood'         => ['nullable', 'string', 'max:40'],
            'waterGlasses' => ['required', 'integer', 'min:0', 'max:100'],
            'symptoms'     => ['nullable', 'array', 'max:30'],
            'symptoms.*'   => ['string', 'max:100'],
            'notes'        => ['nullable', 'string', 'max:2000'],
        ];
    }
}
