<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MedicationSchedule extends Model
{
    protected $fillable = [
        'patient_medication_id',
        'scheduled_time',
        'dose_amount',
        'dose_unit',
        'start_date',
        'end_date',
        'days_of_week',
        'is_active',
    ];

    protected $casts = [
        'dose_amount'  => 'decimal:3',
        'start_date'   => 'date',
        'end_date'     => 'date',
        'days_of_week' => 'array',
        'is_active'    => 'boolean',
    ];

    public function medication()
    {
        return $this->belongsTo(
            PatientMedication::class,
            'patient_medication_id'
        );
    }

    public function logs()
    {
        return $this->hasMany(
            MedicationLog::class,
            'medication_schedule_id'
        );
    }
}
