<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PediatricDoseRule extends Model
{
    protected $fillable = [
        'medicine_id',
        'formulation',
        'indication',
        'concentration_mg',
        'concentration_ml',
        'dose_mg_per_kg_per_dose',
        'max_single_dose_mg',
        'max_daily_mg_per_kg',
        'max_daily_dose_mg',
        'dose_interval_hours',
        'max_doses_per_24_hours',
        'min_age_months',
        'max_age_months',
        'min_weight_kg',
        'max_weight_kg',
        'route',
        'contraindications',
        'notes',
        'source_reference',
        'source_version',
        'approved_by',
        'approved_at',
        'is_active',
        // Workflow fields
        'status',
        'created_by',
        'submitted_at',
        'reviewed_by',
        'reviewed_at',
        'rejection_reason',
        'version',
        'supersedes_rule_id',
    ];

    protected function casts(): array
    {
        return [
            'concentration_mg'        => 'decimal:3',
            'concentration_ml'        => 'decimal:3',
            'dose_mg_per_kg_per_dose' => 'decimal:4',
            'max_single_dose_mg'      => 'decimal:3',
            'max_daily_mg_per_kg'     => 'decimal:4',
            'max_daily_dose_mg'       => 'decimal:3',
            'min_weight_kg'           => 'decimal:3',
            'max_weight_kg'           => 'decimal:3',
            'is_active'               => 'boolean',
            'approved_at'             => 'datetime',
            'submitted_at'            => 'datetime',
            'reviewed_at'             => 'datetime',
        ];
    }

    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function calculations(): HasMany
    {
        return $this->hasMany(
            PediatricDoseCalculation::class,
            'pediatric_dose_rule_id'
        );
    }

    public function scopeApproved($query)
    {
        return $query
            ->where('is_active', true)
            ->whereNotNull('approved_by')
            ->whereNotNull('approved_at');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function supersedes()
    {
        return $this->belongsTo(self::class, 'supersedes_rule_id');
    }

    public function audits()
    {
        return $this->hasMany(
            PediatricDoseRuleAudit::class,
            'pediatric_dose_rule_id'
        )->latest();
    }
}
