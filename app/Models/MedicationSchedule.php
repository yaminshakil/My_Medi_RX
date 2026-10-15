<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MedicationSchedule extends Model
{
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
