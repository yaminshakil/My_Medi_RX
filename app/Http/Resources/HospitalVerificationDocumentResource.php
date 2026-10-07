<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HospitalVerificationDocumentResource extends JsonResource
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
            'hospital_id' => $this->hospital_id,

            'document_type' => $this->document_type,

            'document_type_label' =>
                config(
                    'hospital.document_types.' .
                    $this->document_type
                ),

            'document_title' => $this->document_title,
            'document_number' => $this->document_number,

            'document_path' => $this->document_path,
            'document_url' => $this->document_url,

            'issued_at' => $this->issued_at,
            'expires_at' => $this->expires_at,

            'verification_status' =>
                $this->verification_status,

            'rejection_reason' =>
                $this->rejection_reason,

            'verified_by' => $this->verified_by,
            'verified_at' => $this->verified_at,

            'verifier' => $this->whenLoaded(
                'verifier',
                fn () => [
                    'id' => $this->verifier->id,
                    'name' => $this->verifier->name,
                ]
            ),

            'created_at' => $this->created_at,
        ];
    }
}
