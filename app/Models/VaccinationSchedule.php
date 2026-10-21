<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VaccinationSchedule extends Model
{
    protected $fillable = [
        'vaccine_id',
        'schedule_name',
        'dose_number',
        'recommended_age_days',
        'interval_days',
        'notes',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'dose_number' => 'integer',
            'recommended_age_days' => 'integer',
            'interval_days' => 'integer',
        ];
    }

    public function vaccine()
    {
        return $this->belongsTo(Vaccine::class);
    }

    public function records()
    {
        return $this->hasMany(
            VaccinationRecord::class,
            'vaccination_schedule_id'
        );
    }
}
