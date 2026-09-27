<?php

namespace App\Http\Resources\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserBasicResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'name'              => $this->name,
            'FirstName'         => $this->first_name,
            'LastName'          => $this->last_name,
            'email'             => $this->email,
            'status'            => $this->status,
            'mobile'            => $this->mobile,
            'isPatient'         => $this->isPatient,
            'isAdmin'           => $this->isAdmin,
            'isActive'          => $this->isActive,
            'isInactive'        => $this->isInactive,
            'email_verified_at' => $this->email_verified_at,
            'created_at'        => $this->created_at,
            'updated_at'        => $this->updated_at,
            'profile_image'     => $this->profileImage ? $this->profileImage->path : null,
        ];
    }
}
