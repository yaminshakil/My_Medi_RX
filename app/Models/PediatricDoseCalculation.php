<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class PediatricDoseCalculation extends Model
{
    protected $fillable = [
        'uuid',
        'user_id',
        'medicine_id',
        'pediatric_dose_rule_id',
        'child_weight_kg',
        'child_age_months',
        'indication',
        'formulation',
        'calculated_dose_mg',
        'calculated_volume_ml',
        'concentration_mg',
        'concentration_ml',
        'rule_dose_mg_per_kg',
        'max_single_dose_mg',
        'route',
        'dose_interval_hours',
        'max_doses_per_24_hours',
        'source_reference',
        'status',
        'safety_checks',
        'calculated_at',
    ];

    protected function casts(): array
    {
        return [
            'child_weight_kg' => 'decimal:3',
            'calculated_dose_mg' => 'decimal:3',
            'calculated_volume_ml' => 'decimal:3',
            'safety_checks' => 'array',
            'calculated_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $calculation) {
            $calculation->uuid ??= (string) Str::uuid();
        });
    }

    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class);
    }

    public function rule(): BelongsTo
    {
        return $this->belongsTo(
            PediatricDoseRule::class,
            'pediatric_dose_rule_id'
        );
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
