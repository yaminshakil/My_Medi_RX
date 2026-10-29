<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PharmacyPrescriptionOrder extends Model
{
    protected $fillable = [
        'pharmacy_id',
        'prescription_id',
        'status',
        'received_at',
        'dispensed_at',
    ];

    protected function casts(): array
    {
        return [
            'received_at'  => 'datetime',
            'dispensed_at' => 'datetime',
        ];
    }

    public function pharmacy(): BelongsTo
    {
        return $this->belongsTo(Pharmacy::class);
    }

    public function prescription(): BelongsTo
    {
        return $this->belongsTo(Prescription::class);
    }

    public function dispenses(): HasMany
    {
        return $this->hasMany(PharmacyDispense::class);
    }
}
