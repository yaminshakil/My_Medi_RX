<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DoctorEducation extends Model
{
    use HasFactory;

    protected $table = 'doctor_educations'; // 👈 important

    protected $fillable = [
        'doctor_id',
        'degree',
        'institute',
        'year',
        'country',
    ];

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }
}
