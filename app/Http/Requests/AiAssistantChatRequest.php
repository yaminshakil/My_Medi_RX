<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AiAssistantChatRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'sessionId' => [
                'nullable',
                'string',
                'uuid',
                'exists:ai_chat_sessions,session_id',
            ],
            'message' => [
                'required',
                'string',
                'min:2',
                'max:4000',
            ],
        ];
    }
}
