<?php

namespace App\Http\Requests\Review;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // or $this->user()->isAdmin()
    }

    public function rules(): array
    {
        return [
            'reviewable_type' => ['required', 'string', Rule::in([
                'App\Models\User',
                'App\Models\Product',   // add your other models
                'App\Models\Post',
            ])],
            'reviewable_id'   => ['required', 'integer'],
            'user_id'         => ['required', 'exists:users,id'],
            'rating'          => ['required', 'integer', 'min:1', 'max:5'],
            'comment'         => ['nullable', 'string', 'max:2000'],
            'approved'        => ['required', 'boolean'],
        ];
    }
}
