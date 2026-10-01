<?php

namespace App\Http\Resources\Patients;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class PatientResource extends JsonResource
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
            'uuid'              => $this->uuid,
            'patient_number'    => $this->patient_number,
            'user_id'           => $this->user_id,
            'name'              => $this->name,
            'email'             => $this->email,
            'phone'             => $this->phone,
            'gender'            => $this->gender,
            'blood_group'       => $this->blood_group,
            'marital_status'    => $this->marital_status,
            'city'              => $this->city,
            'address'           => $this->address,
            'date_of_birth'     => $this->date_of_birth?->toISOString(),
            'birth_date'        => $this->date_of_birth?->format('Y-m-d'),

            // Computed age object matching DateInterval output
            'age'               => $this->getAgeArray(),

            // Dynamic date formatting
            'current_date'      => now()->format('d-M-Y'),

            // Image association / Media handle
            'profile_image_url' => $this->profile_image?->image_url ?? $this->profile_image_url,
            'created_at'        => $this->created_at?->toISOString(),
        ];
    }

    /**
     * Helper to compute age interval object matching Carbon/DateInterval diff structure.
     */
    protected function getAgeArray(): ?array
    {
        if (!$this->date_of_birth) {
            return null;
        }

        $dob = Carbon::parse($this->date_of_birth);
        $diff = $dob->diff(now());

        return [
            'y'           => $diff->y,
            'm'           => $diff->m,
            'd'           => $diff->d,
            'h'           => $diff->h,
            'i'           => $diff->i,
            's'           => $diff->s,
            'f'           => $diff->f,
            'invert'      => $diff->invert,
            'days'        => $diff->days,
            'from_string' => false,
        ];
    }
}
