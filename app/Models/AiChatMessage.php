<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiChatMessage extends Model
{
    protected $fillable = [
        'ai_chat_session_id',
        'sender',
        'message',
        'suggested_actions',
    ];

    protected function casts(): array
    {
        return [
            'suggested_actions' => 'array',
        ];
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(
            AiChatSession::class,
            'ai_chat_session_id'
        );
    }
}
