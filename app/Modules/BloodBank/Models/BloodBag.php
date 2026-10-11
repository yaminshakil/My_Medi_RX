<?php

namespace App\Modules\BloodBank\Models;

use App\Modules\BloodBank\Enums\BagStatus;
use App\Modules\BloodBank\Enums\BloodComponent;
use App\Modules\BloodBank\Enums\BloodGroup;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class BloodBag extends Model
{
    protected $table = 'bb_blood_bags';

    protected $fillable = [
        'bag_number', 'batch_number', 'donor_id', 'blood_group', 'component', 'volume_ml',
        'collected_at', 'expires_at', 'storage_unit_id', 'status', 'discard_reason',
        'discarded_at', 'registered_by',
    ];

    protected function casts(): array
    {
        return [
            'blood_group'  => BloodGroup::class,
            'component'    => BloodComponent::class,
            'status'       => BagStatus::class,
            'collected_at' => 'datetime',
            'expires_at'   => 'datetime',
            'discarded_at' => 'datetime',
        ];
    }

    public function storageUnit(): BelongsTo
    {
        return $this->belongsTo(StorageUnit::class);
    }

    public function donor(): BelongsTo
    {
        return $this->belongsTo(BloodDonor::class, 'donor_id');
    }

    public function issue(): HasOne
    {
        return $this->hasOne(BloodIssue::class);
    }

    /** Usable stock = marked available AND not past its expiry timestamp. */
    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('status', BagStatus::Available->value)->where('expires_at', '>', now());
    }

    /** Whole hours until expiry; negative once expired. */
    public function hoursLeft(): int
    {
        return (int) floor(now()->diffInHours($this->expires_at, false));
    }

    /** expired | critical | warning | ok, driven by config/blood-bank.php. */
    public function expiryState(): string
    {
        if ($this->expires_at->isPast()) {
            return 'expired';
        }

        $hours = $this->hoursLeft();

        if ($hours <= config('blood-bank.expiry_critical_hours')) {
            return 'critical';
        }

        $alertDays = config('blood-bank.expiry_alert_days.'.$this->component->value, 7);

        return $hours <= $alertDays * 24 ? 'warning' : 'ok';
    }
}
