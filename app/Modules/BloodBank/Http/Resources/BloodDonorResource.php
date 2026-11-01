<?php

namespace App\Modules\BloodBank\Http\Resources;

use App\Modules\BloodBank\Models\BloodDonor;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin BloodDonor */
class BloodDonorResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'name'            => $this->name,
            'phone'           => $this->phone,
            'email'           => $this->email,
            'blood_group'     => $this->blood_group->value,
            'latitude'        => $this->latitude,
            'longitude'       => $this->longitude,
            'city'            => $this->city,
            'last_donated_at' => $this->last_donated_at?->toDateString(),
            'is_verified'     => $this->is_verified,
            'verified_at'     => $this->verified_at?->toIso8601String(),
            'is_available'    => $this->is_available,
        ];
    }
}
