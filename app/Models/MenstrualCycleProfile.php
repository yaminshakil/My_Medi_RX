<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MenstrualCycleProfile extends Model
{
    protected $fillable = [
        'user_id',
        'last_period_start',
        'cycle_length',
        'period_length',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'last_period_start' => 'date',
            'cycle_length'      => 'integer',
            'period_length'     => 'integer',
            'is_active'         => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(MenstrualCycleLog::class);
    }
}
