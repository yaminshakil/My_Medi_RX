<?php

namespace App\Modules\BloodBank\Models;

use App\Models\User;
use App\Modules\BloodBank\Enums\BloodComponent;
use App\Modules\BloodBank\Enums\BloodGroup;
use App\Modules\BloodBank\Enums\EmergencyStatus;
use App\Modules\BloodBank\Enums\UrgencyLevel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class EmergencyRequest extends Model
{
    protected $table = 'bb_emergency_requests';

    protected $fillable = [
        'reference', 'hospital_id', 'requested_by', 'blood_group', 'component', 'units_needed',
        'urgency', 'radius_km', 'note', 'status', 'donors_notified_count',
        'broadcast_at', 'expires_at', 'closed_at',
    ];

    protected function casts(): array
    {
        return [
            'blood_group'  => BloodGroup::class,
            'component'    => BloodComponent::class,
            'urgency'      => UrgencyLevel::class,
            'status'       => EmergencyStatus::class,
            'radius_km'    => 'float',
            'broadcast_at' => 'datetime',
            'expires_at'   => 'datetime',
            'closed_at'    => 'datetime',
        ];
    }

    public function hospital(): BelongsTo
    {
        return $this->belongsTo(Hospital::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function donors(): BelongsToMany
    {
        return $this->belongsToMany(BloodDonor::class, 'bb_emergency_request_donor', 'emergency_request_id', 'blood_donor_id')
            ->withPivot(['distance_km', 'channel', 'notified_at', 'response', 'responded_at']);
    }

    /** Still accepting donor replies. */
    public function isOpen(): bool
    {
        return $this->status === EmergencyStatus::Open && $this->expires_at->isFuture();
    }
}
