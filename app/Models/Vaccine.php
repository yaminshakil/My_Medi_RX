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
        'manufacturer_id',
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

    public function manufacturer()
    {
        return $this->belongsTo(Manufacturer::class, 'manufacturer_id', 'id')
            ->select('id', 'company_name as name');
    }
}
