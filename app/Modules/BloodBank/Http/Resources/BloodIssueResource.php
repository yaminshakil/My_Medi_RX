<?php

namespace App\Modules\BloodBank\Http\Resources;

use App\Modules\BloodBank\Models\BloodIssue;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin BloodIssue */
class BloodIssueResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'issue_number' => $this->issue_number,
            'issued_at'    => $this->issued_at->toIso8601String(),
            'bag'          => [
                'id'              => $this->bag->id,
                'bag_number'      => $this->bag->bag_number,
                'batch_number'    => $this->bag->batch_number,
                'blood_group'     => $this->bag->blood_group->value,
                'component_label' => $this->bag->component->label(),
                'storage_unit'    => $this->bag->storageUnit?->name,
            ],
            'patient' => [
                'id'               => $this->patient->id,
                'name'             => $this->patient->name,
                'mrn'              => $this->patient->mrn,
                'clinical_file_no' => $this->patient->clinical_file_no,
                'blood_group'      => $this->patient->blood_group?->value,
            ],
            'physician' => [
                'id'         => $this->physician->id,
                'name'       => $this->physician->name,
                'department' => $this->physician->department,
            ],
            'issued_by'                     => $this->issuer?->name,
            'ward'                          => $this->ward,
            'indication'                    => $this->indication,
            'compatibility_override_reason' => $this->compatibility_override_reason,
            'transfusion_started_at'        => $this->transfusion_started_at?->format('Y-m-d\TH:i'),
            'transfusion_completed_at'      => $this->transfusion_completed_at?->format('Y-m-d\TH:i'),
            'reaction_noted'                => $this->reaction_noted,
            'reaction_notes'                => $this->reaction_notes,
        ];
    }
}
