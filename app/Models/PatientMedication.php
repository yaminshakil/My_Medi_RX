<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PatientMedication extends Model
{
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function prescriptionMedicine()
    {
        return $this->belongsTo(
            PrescriptionMedicine::class,
            'prescription_medicine_id'
        );
    }

    public function schedules()
    {
        return $this->hasMany(
            MedicationSchedule::class,
            'patient_medication_id'
        );
    }

    public function logs()
    {
        return $this->hasMany(
            MedicationLog::class,
            'patient_medication_id'
        );
    }
}
