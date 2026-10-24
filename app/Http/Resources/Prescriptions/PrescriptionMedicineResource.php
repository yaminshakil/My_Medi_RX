<?php

namespace App\Http\Resources\Prescriptions;

use App\Http\Resources\Medicines\MedicineResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PrescriptionMedicineResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'medicine_id'     => $this->medicine_id,
            'prescription_id' => $this->prescription_id,
            'type'            => $this->type,
            'strength'        => $this->strength,
            'dosage'          => $this->dosage,
            'duration'        => $this->duration,
            'advice'          => $this->advice,
            'meal_time'       => $this->meal_time,

            // Relationships (loaded conditionally)
            'medicine' => new MedicineResource($this->whenLoaded('medicines')),
        ];
    }
}
