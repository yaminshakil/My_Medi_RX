<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DoctorRoster extends Model
{
    protected $fillable = [
        'hospital_id', 'department_id', 'doctor_id', 'shift_id',
        'roster_date', 'start_time', 'end_time', 'room_label',
        'status', 'notes', 'created_by',
    ];

    protected function casts(): array { return ['roster_date' => 'date']; }

    public function hospital(): BelongsTo { return $this->belongsTo(Hospital::class); }
    public function department(): BelongsTo { return $this->belongsTo(Department::class); }
    public function doctor(): BelongsTo { return $this->belongsTo(User::class, 'doctor_id'); }
    public function shift(): BelongsTo { return $this->belongsTo(DoctorShift::class, 'shift_id'); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
}
