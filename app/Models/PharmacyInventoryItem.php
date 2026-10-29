<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PharmacyInventoryItem extends Model
{
    protected $fillable = [
        'pharmacy_id',
        'medicine_name',
        'generic_name',
        'strength',
        'stock_quantity',
        'reorder_level',
        'expiry_date',
    ];

    protected function casts(): array
    {
        return [
            'stock_quantity' => 'integer',
            'reorder_level'  => 'integer',
            'expiry_date'    => 'date',
        ];
    }

    public function pharmacy(): BelongsTo
    {
        return $this->belongsTo(Pharmacy::class);
    }

    public function dispenses(): HasMany
    {
        return $this->hasMany(PharmacyDispense::class);
    }
}
