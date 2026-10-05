<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DoctorChamber extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'city',
        'address',
        'header_left',
        'header_right',
        'footer_info',
        'chamber_logo',
        'doctor_id',
        'schedules',
        'fee',
        'followup_fee',
        'report_fee',
        'appoinment_limit',
        'is_active',
    ];

    protected $casts = [
        'schedules' => 'array', // automatically cast JSON <-> array
    ];

    protected $appends = ['chamber_logo_url'];

    public function getChamberLogoUrlAttribute()
    {
        return $this->chamber_logo ? asset('storage/'.$this->chamber_logo) : null;
    }
}
