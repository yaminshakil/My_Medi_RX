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

    public function medicines()
    {
        return $this->belongsTo(Medicine::class, 'medicine_id');
    }
}
