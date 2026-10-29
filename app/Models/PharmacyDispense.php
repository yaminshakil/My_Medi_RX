<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PharmacyDispense extends Model
{
    protected $fillable = [
        'pharmacy_id',
        'pharmacy_prescription_order_id',
        'pharmacy_inventory_item_id',
        'dispensed_by',
        'quantity',
        'dispensed_at',
    ];

    protected function casts(): array
    {
        return [
            'quantity'     => 'integer',
            'dispensed_at' => 'datetime',
        ];
    }

    public function pharmacy(): BelongsTo
    {
        return $this->belongsTo(Pharmacy::class);
    }

    public function prescriptionOrder(): BelongsTo
    {
        return $this->belongsTo(PharmacyPrescriptionOrder::class, 'pharmacy_prescription_order_id');
    }

    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(PharmacyInventoryItem::class, 'pharmacy_inventory_item_id');
    }

    public function dispensedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dispensed_by');
    }
}
