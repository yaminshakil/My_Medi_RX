<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MedicationLog extends Model
{
    protected $fillable = [
        'patient_id',
        'patient_medication_id',
        'medication_schedule_id',
        'scheduled_date',
        'scheduled_at',
        'taken_at',
        'status',
        'snoozed_until',
        'notes',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'scheduled_at'   => 'datetime',
        'taken_at'       => 'datetime',
        'snoozed_until'  => 'datetime',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

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
