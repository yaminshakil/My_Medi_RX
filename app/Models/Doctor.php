<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Doctor extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'phone',
        'registration_no',
        'gender',
        'dob',
        'specialization',
        'working_institute',
        'designation',
        'qualification',
        'experience_years',
        'bio',
        'social',
        'active',
        'featured',
    ];

    protected $casts = [
        'social'   => 'array', // automatically cast JSON to array
        'active'   => 'boolean',
        'featured' => 'boolean',
    ];

    // ✅ Scope for active Doctor
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    // ✅ Scope for active Doctor
    public function scopeFeatured($query)
    {
        return $query->where('featured', true);
    }

    // ✅ Tell Laravel to use uuid instead of id for route binding
    public function getRouteKeyName()
    {
        return 'uuid';
    }

    protected static function booted()
    {
        static::creating(function ($assistant) {
            if (empty($assistant->uuid)) {
                $assistant->uuid = (string) Str::uuid();
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function educations()
    {
        return $this->hasMany(DoctorEducation::class);
    }

    public function experiences()
    {
        return $this->hasMany(DoctorExperience::class);
    }

    public function getIsActiveAttribute(): bool
    {
        if (! isset($this->active)) {
            return false;
        }

        return $this->active === true;
    }
}
