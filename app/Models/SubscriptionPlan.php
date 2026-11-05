<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubscriptionPlan extends Model
{
    protected $fillable = ['name', 'slug', 'description', 'price', 'billing_cycle', 'duration_days', 'user_type', 'features', 'is_active'];

    protected $casts = ['price' => 'decimal:2', 'features' => 'array', 'is_active' => 'boolean'];

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }
}
