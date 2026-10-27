<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FirebaseDeviceToken extends Model
{
    protected $fillable = [
        'user_id',
        'token',
        'token_hash',
    ];

    protected $hidden = [
        'token',
        'token_hash',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
