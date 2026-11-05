<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubscriptionPlanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'name'          => $this->name,
            'slug'          => $this->slug,
            'description'   => $this->description,
            'price'         => (float) $this->price,
            'currency'      => config('sslcommerz.currency', 'BDT'),
            'billing_cycle' => $this->billing_cycle,
            'duration_days' => $this->duration_days,
            'user_type'     => $this->user_type,
            'features'      => $this->features ?? [],
            'is_active'     => (bool) $this->is_active,
        ];
    }
}
