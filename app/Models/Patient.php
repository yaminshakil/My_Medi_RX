<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Patient extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name', 'date_of_birth', 'age', 'gender',
        'phone', 'email', 'address', 'city', 'state', 'postal_code',
        'country', 'blood_group', 'marital_status',
        'emergency_contact_name', 'emergency_contact_phone', 'emergency_contact_relationship',
        'user_id', 'patient_number', 'relationship',
    ];

    protected $appends = ['age', 'birth_date', 'profile_image_url', 'current_date'];

    protected $casts = [
        'date_of_birth' => 'date', // Ensures Carbon instance
    ];

    public function prescriptions()
    {
        return $this->hasMany(Prescription::class)->orderBy('created_at', 'desc');
    }

    public function vitals()
    {
        return $this->hasMany(Vital::class)->orderBy('created_at', 'desc');
    }

    // Accessor: get age in years
    public function getAgeAttribute()
    {
        return $this->date_of_birth ? $this->date_of_birth->diff(Carbon::now()) : null;
    }

    // Accessor: get Date Of Birth in years
    public function getBirthDateAttribute()
    {
        return $this->date_of_birth ? $this->date_of_birth->format('Y-m-d') : null;
    }

    public function vital()
    {
        return $this->hasOne(Vital::class, 'patient_id')->whereDate('created_at', Carbon::today());
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

    public function latestVital()
    {
        return $this->hasOne(Vital::class)->latest();
    }

    public function profile_image()
    {
        return $this->morphOne(Image::class, 'imageable');
    }

    public function getProfileImageUrlAttribute()
    {
        return $this->profile_image
            ? asset('storage/'.$this->profile_image->path)
            : asset('images/patient.png');
    }

    public function getCurrentDateAttribute()
    {
        return Carbon::today()->format('d-M-Y');
    }
}
