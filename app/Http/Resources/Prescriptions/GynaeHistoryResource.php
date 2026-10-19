<?php

namespace App\Http\Resources\Prescriptions;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GynaeHistoryResource extends JsonResource
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
            'prescription_id' => $this->prescription_id,

            // Marriage Details
            'marital_status' => $this->marital_status,
            'marriage_duration' => $this->marriage_duration,
            'consanguinity' => (bool) $this->consanguinity,

            // Menstrual History
            'menarche_age' => $this->menarche_age,
            'lmp' => $this->lmp?->format('Y-m-d'),
            'cycle' => $this->cycle,
            'flow' => $this->flow,
            'dysmenorrhea' => (bool) $this->dysmenorrhea,
            'contraceptive_use' => (bool) $this->contraceptive_use,

            // Obstetrical History
            'gravida' => $this->gravida,
            'para' => $this->para,
            'abortion' => $this->abortion,
            'living_children' => $this->living_children,

            // Current Pregnancy
            'edd' => $this->edd,
            'anc' => $this->anc,

            // Notes
            'other_history' => $this->other_history,

            // Relationship (loaded conditionally)
            'prescription' => new PrescriptionResource($this->whenLoaded('prescription')),

            // Timestamps
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
