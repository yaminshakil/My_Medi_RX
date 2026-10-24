<?php

namespace App\Http\Resources\Prescriptions;

use App\Http\Resources\Patients\PatientResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VitalResource extends JsonResource
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
            'patient_id'        => $this->patient_id,
            'blood_pressure'    => $this->blood_pressure,
            'heart_rate'        => $this->heart_rate,
            'temperature'       => $this->temperature,
            'respiratory_rate'  => $this->respiratory_rate,
            'oxygen_saturation' => $this->oxygen_saturation,
            'weight'            => $this->weight,
            'height'            => $this->height,
            'bmi'               => $this->bmi,
            'notes'             => $this->notes,
            // Conditionally include the patient relationship if it is loaded
            // 'patient' => new PatientResource($this->whenLoaded('patient')),
        ];
    }
}
