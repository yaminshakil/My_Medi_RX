<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use URL;

class HospitalResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'hospital_name' => $this->hospital_name,
            'hospital_type_id' => $this->hospital_type_id,
            'hospital_type' => $this->whenLoaded(
                'hospitalType',
                fn () => [
                    'id' => $this->hospitalType->id,
                    'name' => $this->hospitalType->name,
                ]
            ),
            'email' => $this->email,
            'mobile_number' => $this->mobile_number,
            'emergency_contact' => $this->emergency_contact,
            'phone_number' => $this->phone_number,
            'hospital_description' => $this->hospital_description,
            'hospital_logo' => $this->hospital_logo,
            'logo_image_url' => $this->logo_image_url,
            'logo_crop_data' => $this->logo_crop_data,
            'banner_url' => $this->banner_url,
            'banner_image_url' => $this->banner_image_url,
            'banner_crop_data' => $this->banner_crop_data,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'registration_no' => $this->registration_no,
            'service_time' => $this->service_time,
            'organization_notice' => $this->organization_notice,
            'address' => $this->address,
            'hospital_url' => $this->hospital_url,
            'thana_id' => $this->thana_id,
            'district_id' => $this->district_id,
            'division_id' => $this->division_id,
            'sort_order' => $this->sort_order,
            'status' => $this->status,
            'verification_status' => $this->verification_status,
            'verified_at' => $this->verified_at,
            'verified_by' => $this->verified_by,
            'verification_note' => $this->verification_note,
            'doctors' => $this->whenLoaded('doctors'),
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'verification_url' => Auth::user()->can('hospitals.verification')
        ? URL::route('hospital.verification', $this->uuid)
        : null,
            'edit_url' => Auth::user()->can('edit') ? URL::route('hospital.edit', $this->uuid) : null,
            'is_admin' => Auth::user()->hasRole('admin'),
        ];
    }
}
