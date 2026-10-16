<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DoctorShift extends Model
{
    protected $fillable = [
        'hospital_id', 'name', 'start_time', 'end_time',
        'break_start', 'break_end', 'is_active',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function hospital(): BelongsTo
    {
        return $this->belongsTo(Hospital::class);
    }

    public function availabilities(): HasMany
    {
        return $this->hasMany(DoctorAvailability::class, 'shift_id');
    }

    public function rosters(): HasMany
    {
        return $this->hasMany(DoctorRoster::class, 'shift_id');
    }
}
