<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PatientMedication extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'patient_id',
        'prescription_medicine_id',
        'medicine_name',
        'strength',
        'dosage',
        'dosage_unit',
        'source',
        'start_date',
        'end_date',
        'total_quantity',
        'remaining_quantity',
        'refill_threshold',
        'instructions',
        'status',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'total_quantity' => 'integer',
        'remaining_quantity' => 'integer',
        'refill_threshold' => 'integer',
    ];

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
