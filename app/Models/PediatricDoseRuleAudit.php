<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PediatricDoseRuleAudit extends Model
{
    protected $fillable = [
        'pediatric_dose_rule_id',
        'user_id',
        'action',
        'from_status',
        'to_status',
        'reason',
        'changes',
        'ip_address',
    ];

    protected function casts(): array
    {
        return [
            'changes' => 'array',
        ];
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
