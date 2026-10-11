<?php

namespace App\Modules\BloodBank\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

/** One row per dispensed unit. This is the audit log, so rows can be amended (outcome) but never deleted. */
class BloodIssue extends Model
{
    protected $table = 'bb_blood_issues';

    protected $fillable = [
        'issue_number', 'blood_bag_id', 'patient_id', 'physician_id', 'hospital_id',
        'emergency_request_id', 'issued_by', 'indication', 'ward',
        'compatibility_override_reason', 'issued_at', 'transfusion_started_at',
        'transfusion_completed_at', 'reaction_noted', 'reaction_notes',
    ];

    protected static function booted(): void
    {
        static::deleting(function () {
            throw new LogicException('Issue log entries are part of the audit trail and cannot be deleted.');
        });
    }

    protected function casts(): array
    {
        return [
            'issued_at'                => 'datetime',
            'transfusion_started_at'   => 'datetime',
            'transfusion_completed_at' => 'datetime',
            'reaction_noted'           => 'boolean',
        ];
    }

    public function bag(): BelongsTo
    {
        return $this->belongsTo(BloodBag::class, 'blood_bag_id');
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function physician(): BelongsTo
    {
        return $this->belongsTo(Physician::class);
    }

    public function hospital(): BelongsTo
    {
        return $this->belongsTo(Hospital::class);
    }

    public function issuer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    public function emergencyRequest(): BelongsTo
    {
        return $this->belongsTo(EmergencyRequest::class);
    }
}
