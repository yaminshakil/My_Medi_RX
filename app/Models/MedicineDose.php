<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicineDose extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'active',
        'doctor_id',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    // ✅ Scope for active banners
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }
}
