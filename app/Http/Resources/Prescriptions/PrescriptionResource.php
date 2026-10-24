<?php

namespace App\Http\Resources\Prescriptions;

use App\Http\Resources\Doctors\DoctorResource;
use App\Http\Resources\Patients\PatientResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PrescriptionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                  => $this->id,
            'uuid'                => $this->uuid,
            'prescription_number' => $this->prescription_number,
            'patient_id'          => $this->patient_id,
            'doctor_id'           => $this->doctor_id,
            'appointment_id'      => $this->appointment_id,
            'chamber_id'          => $this->chamber_id,
            'vital_id'            => $this->vital_id,

            // Core prescription details
            'followup_date' => $this->followup_date?->format('Y-m-d'),
            'is_followup'   => (bool) $this->is_followup,
            'status'        => $this->status,
            'diagnosis'     => $this->diagnosis,

            // Array casts
            'symptoms'       => $this->symptoms,
            'onexaminations' => $this->onexaminations,
            'investigations' => $this->investigations,

            'instructions'     => $this->instructions,
            'follow_up_advice' => $this->follow_up_advice,

            // Header / Footer configurations
            'header_left'  => $this->header_left,
            'header_right' => $this->header_right,
            'footer_info'  => $this->footer_info,
            'chamber_logo' => $this->chamber_logo,

            // Relationships (loaded conditionally to prevent N+1 issues and keep responses lightweight)
            'patient' => new PatientResource($this->whenLoaded('patient')),
            'doctor'  => $this->whenLoaded('doctor', function () {
                $doctor = $this->doctor;

                return [
                    'id'      => $doctor->id,
                    'name'    => $doctor->name,
                    'email'   => $doctor->email,
                    'profile' => $doctor->doctor
                        ? new DoctorResource($doctor->doctor)
                        : null,
                ];
            }),
            'vitals'        => new VitalResource($this->whenLoaded('vitals')),
            'medications'   => PrescriptionMedicineResource::collection($this->whenLoaded('medications')),
            'gynae_history' => new GynaeHistoryResource($this->whenLoaded('gynaeHistory')),

            // Timestamps
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'deleted_at' => $this->when($this->deleted_at, fn () => $this->deleted_at?->toIso8601String()),
        ];
    }
}
