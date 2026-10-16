<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DoctorAvailability extends Model
{
    protected $fillable = [
        'hospital_id', 'department_id', 'doctor_id', 'shift_id',
        'day_of_week', 'start_time', 'end_time', 'slot_duration',
        'max_patients', 'room_label', 'is_available',
    ];

    protected function casts(): array
    {
        return ['day_of_week' => 'integer', 'slot_duration' => 'integer',
            'max_patients'    => 'integer', 'is_available' => 'boolean'];
    }

    public function hospital(): BelongsTo
    {
        return $this->belongsTo(Hospital::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(DoctorShift::class, 'shift_id');
    }
}
