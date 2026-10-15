<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MedicationLog extends Model
{
    protected $casts = [
    'scheduled_at' => 'datetime',
    'taken_at' => 'datetime',
    'snoozed_until' => 'datetime',
    'scheduled_date' => 'date',
];

    public function medication()
    {
        return $this->belongsTo(
            PatientMedication::class,
            'patient_medication_id'
        );
    }

    public function schedule()
    {
        return $this->belongsTo(
            MedicationSchedule::class,
            'medication_schedule_id'
        );
    }
}
