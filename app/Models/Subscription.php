<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subscription extends Model
{
    protected $fillable = ['user_id', 'subscription_plan_id', 'starts_at', 'ends_at', 'status', 'auto_renew'];

    protected $casts = ['starts_at' => 'datetime', 'ends_at' => 'datetime', 'auto_renew' => 'boolean'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'subscription_plan_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(SubscriptionPayment::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active')->where('ends_at', '>', now());
    }

    public function isActive(): bool
    {
        return $this->status === 'active' && $this->ends_at?->isFuture();
    }
}
