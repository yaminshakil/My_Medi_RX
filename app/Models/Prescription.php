<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Prescription extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'patient_id',
        'doctor_id',
        'followup_date',
        'diagnosis',
        'symptoms',
        'vital_id',
        'instructions',
        'follow_up_advice',
        'status',
        'onexaminations',
        'investigations',
        'prescription_number',
        'appointment_id',
        'is_followup',
        'chamber_id',
        'header_left',
        'header_right',
        'header_right',
        'footer_info',
        'chamber_logo',
    ];

    protected $casts = [
        'followup_date' => 'date',
        'symptoms' => 'array',
        'onexaminations' => 'array',
        'investigations' => 'array',
    ];

    // ✅ Tell Laravel to use uuid instead of id for route binding
    public function getRouteKeyName()
    {
        return 'uuid';
    }

    protected static function booted()
    {
        static::creating(function ($prescription) {
            if (empty($prescription->uuid)) {
                $prescription->uuid = (string) Str::uuid();
            }
        });
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function vitals()
    {
        return $this->belongsTo(Vital::class, 'vital_id');
    }

    public function medications()
    {
        return $this->hasMany(PrescriptionMedicine::class, 'prescription_id');
    }



    public function gynaeHistory()
    {
        return $this->hasOne(GynaeHistory::class);
    }
}
