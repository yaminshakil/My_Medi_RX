<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PregnancySymptomLog extends Model
{
    protected $fillable = [
        'uuid',
        'pregnancy_profile_id',
        'date',
        'symptoms',
        'mood',
        'fetal_kick_count',
        'water_intake_liters',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'symptoms' => 'array',
            'fetal_kick_count' => 'integer',
            'water_intake_liters' => 'decimal:2',
        ];
    }

    public function pregnancyProfile(): BelongsTo
    {
        return $this->belongsTo(PregnancyProfile::class);
    }
}
