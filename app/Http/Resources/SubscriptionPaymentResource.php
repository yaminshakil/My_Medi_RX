<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubscriptionPaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'subscription_id' => $this->subscription_id,
            'transaction_id' => $this->transaction_id,
            'amount' => (float) $this->amount,
            'currency' => $this->currency,
            'status' => $this->status,
            'payment_method' => $this->payment_method,
            'paid_at' => $this->paid_at?->toISOString(),
            'plan' => $this->whenLoaded('subscription', fn () => $this->subscription?->plan?->name),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
