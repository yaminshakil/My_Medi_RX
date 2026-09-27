<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DoctorExperience extends Model
{
    use HasFactory;

    protected $table = 'doctor_experiences';

    protected $fillable = [
        'doctor_id',
        'hospital_name',
        'position',
        'from_year',
        'to_year',
        'description',
    ];

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }
}
