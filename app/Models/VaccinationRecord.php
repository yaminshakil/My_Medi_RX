<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class VaccinationRecord extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'patient_id',
        'vaccine_id',
        'vaccination_schedule_id',
        'dose_number',
        'scheduled_date',
        'administered_at',
        'status',
        'provider_name',
        'facility_name',
        'batch_number',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_date' => 'date',
            'administered_at' => 'datetime',
            'dose_number' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (VaccinationRecord $record) {
            $record->uuid ??= (string) Str::uuid();
        });
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function vaccine()
    {
        return $this->belongsTo(Vaccine::class);
    }

    public function schedule()
    {
        return $this->belongsTo(
            VaccinationSchedule::class,
            'vaccination_schedule_id'
        );
    }
}
