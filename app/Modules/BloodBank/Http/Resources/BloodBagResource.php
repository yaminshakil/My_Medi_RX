<?php

namespace App\Modules\BloodBank\Http\Resources;

use App\Modules\BloodBank\Models\BloodBag;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin BloodBag */
class BloodBagResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'bag_number'      => $this->bag_number,
            'batch_number'    => $this->batch_number,
            'blood_group'     => $this->blood_group->value,
            'component'       => $this->component->value,
            'component_label' => $this->component->label(),
            'volume_ml'       => $this->volume_ml,
            'collected_at'    => $this->collected_at->toIso8601String(),
            'expires_at'      => $this->expires_at->toIso8601String(),
            'hours_left'      => $this->hoursLeft(),
            'expiry_state'    => $this->expiryState(),
            'status'          => $this->status->value,
            'storage_unit'    => $this->storageUnit?->name,
            'donor'           => $this->donor?->name,
            'discard_reason'  => $this->discard_reason,
        ];
    }
}
