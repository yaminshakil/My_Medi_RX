<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory;

    protected $fillable = [
        'doctor_id',
        'patient_id',
        'chamber_id',
        'appointment_date',
        'appointment_time',
        'status',
        'consultation_type',
        'notes',
        'fee',
        'is_paid',
        'appointment_number',
        'appointment_type',
    ];

    protected $casts = [
        'appointment_date' => 'date',
        'appointment_time' => 'datetime:h:i A',
        'is_paid' => 'boolean',
    ];

    protected $appends = ['appointmentdatetime', 'bookingdate', 'bookingtime'];

    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    public function chamber()
    {
        return $this->belongsTo(DoctorChamber::class, 'chamber_id');
    }

    public function getAppointmentDatetimeAttribute()
    {
        return $this->appointment_date->format('Y-m-d').' '.$this->appointment_time->format('H:i:s');
    }

    public function getBookingDateAttribute()
    {
        return $this->appointment_date->format('Y-m-d').' '.$this->appointment_time->format('H:i:s');
    }

    public function getBookingTimeAttribute()
    {
        return $this->appointment_time->format('h:i:s A');
    }
}
