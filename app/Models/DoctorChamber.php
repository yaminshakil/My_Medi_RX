<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class DoctorChamber extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'hospital_id',
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
        'user_id',
    ];

    protected $casts = [
        'schedules' => 'array', // automatically cast JSON <-> array
    ];

    protected $appends = ['chamber_logo_url'];

    public function hospital(): BelongsTo
    {
        return $this->belongsTo(Hospital::class, 'hospital_id');
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class, 'chamber_id');
    }

    public function getChamberLogoUrlAttribute()
    {
        return $this->chamber_logo ? asset('storage/'.$this->chamber_logo) : null;
    }
}
