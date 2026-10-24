<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MenstrualCycleLog extends Model
{
    protected $fillable = [
        'uuid',
        'menstrual_cycle_profile_id',
        'entry_date',
        'flow',
        'cramps',
        'mood',
        'water_glasses',
        'symptoms',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'entry_date'    => 'date',
            'water_glasses' => 'integer',
            'symptoms'      => 'array',
        ];
    }

    public function profile(): BelongsTo
    {
        return $this->belongsTo(
            MenstrualCycleProfile::class,
            'menstrual_cycle_profile_id'
        );
    }
}
