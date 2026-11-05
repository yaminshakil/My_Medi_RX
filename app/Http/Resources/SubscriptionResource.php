<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubscriptionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'starts_at' => $this->starts_at?->toISOString(),
            'ends_at' => $this->ends_at?->toISOString(),
            'cancel_at_period_end' => (bool) $this->cancel_at_period_end,
            'is_current' => $this->isActive() && (!$this->starts_at || $this->starts_at->isPast()),
            'plan' => new SubscriptionPlanResource($this->whenLoaded('plan')),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
