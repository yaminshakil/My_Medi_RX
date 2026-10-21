<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Vaccine extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'manufacturer',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Vaccine $vaccine) {
            $vaccine->uuid ??= (string) Str::uuid();
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function schedules()
    {
        return $this->hasMany(VaccinationSchedule::class);
    }

    public function records()
    {
        return $this->hasMany(VaccinationRecord::class);
    }
}
