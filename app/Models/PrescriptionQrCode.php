<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrescriptionQrCode extends Model
{
    protected $fillable = [
        'prescription_id',
        'token',
        'expires_at',
        'revoked_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }

    public function prescription(): BelongsTo
    {
        return $this->belongsTo(Prescription::class);
    }

    public function isValid(): bool
    {
        return $this->revoked_at === null
            && (
                $this->expires_at === null
                || $this->expires_at->isFuture()
            );
    }
}
