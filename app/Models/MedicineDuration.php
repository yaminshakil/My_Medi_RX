<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicineDuration extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'doctor_id',
        'days',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // ✅ Scope for active banners
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
