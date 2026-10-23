<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PregnancyProfile extends Model
{
    protected $fillable = [
        'user_id',
        'last_menstrual_period',
        'due_date',
        'dating_confirmed_date',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'last_menstrual_period' => 'date',
            'due_date' => 'date',
            'dating_confirmed_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function symptomLogs(): HasMany
    {
        return $this->hasMany(PregnancySymptomLog::class);
    }
}
