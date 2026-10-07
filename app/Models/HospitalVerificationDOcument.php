<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class HospitalVerificationDocument extends Model
{
    protected $fillable = [
        'hospital_id',
        'document_type',
        'document_title',
        'document_number',
        'document_path',
        'issued_at',
        'expires_at',
        'verification_status',
        'rejection_reason',
        'verified_by',
        'verified_at',
    ];

    protected $casts = [
        'issued_at' => 'date',
        'expires_at' => 'date',
        'verified_at' => 'datetime',
    ];

    protected $appends = [
        'document_url',
    ];

    public function hospital(): BelongsTo
    {
        return $this->belongsTo(Hospital::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function getDocumentUrlAttribute(): ?string
    {
        return $this->document_path
            ? Storage::url($this->document_path)
            : null;
    }
}
