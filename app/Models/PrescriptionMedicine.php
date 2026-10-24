<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrescriptionMedicine extends Model
{
    use HasFactory;

    protected $fillable = [
        'medicine_id',
        'prescription_id',
        'type',
        'strength',
        'dosage',
        'duration',
        'advice',
        'meal_time',
    ];

    // public function medicines()
    // {
    //     return $this->belongsTo(Medicine::class, 'medicine_id');
    // }

    public function prescription()
    {
        return $this->belongsTo(Prescription::class, 'prescription_id');
    }

    public function medicine()
    {
        return $this->belongsTo(Medicine::class, 'medicine_id');
    }

    // Keep compatibility with your existing code.
    public function medicines()
    {
        return $this->medicine();
    }

    public function patientMedications()
    {
        return $this->hasMany(
            PatientMedication::class,
            'prescription_medicine_id',
            'id'
        );
    }
}
