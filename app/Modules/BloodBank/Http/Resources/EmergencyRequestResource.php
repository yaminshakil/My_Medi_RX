<?php

namespace App\Modules\BloodBank\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Modules\BloodBank\Models\EmergencyRequest */
class EmergencyRequestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                    => $this->id,
            'reference'             => $this->reference,
            'hospital'              => $this->hospital?->name,
            'blood_group'           => $this->blood_group->value,
            'component_label'       => $this->component->label(),
            'units_needed'          => $this->units_needed,
            'urgency'               => $this->urgency->value,
            'status'                => $this->isOpen() ? 'open' : ($this->status->value === 'open' ? 'expired' : $this->status->value),
            'radius_km'             => $this->radius_km,
            'note'                  => $this->note,
            'donors_notified_count' => $this->donors_notified_count,
            'accepted_count'        => (int) ($this->accepted_count ?? 0),
            'broadcast_at'          => $this->broadcast_at->toIso8601String(),
            'expires_at'            => $this->expires_at->toIso8601String(),
        ];
    }
}
