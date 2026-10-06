<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Hospital extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'hospital_name',
        'address',
        'thana_id',
        'district_id',
        'division_id',
        'hospital_description',
        'hospital_url',
        'mobile_number',
        'emergency_contact',
        'phone_number',
        'hospital_logo',
        'banner_url',
        'banner_crop_data',
        'sort_order',
        'status',
        'latitude',
        'longitude',
        'registration_no',
        'service_time',
        'organization_notice',
        'created_by',
        'updated_by'
    ];

    protected $dates = ['deleted_at'];

    protected $casts = [
        'banner_crop_data' => 'array'
    ];

    protected $appends = ['logo_image_url'];

    public function doctors()
    {
        return $this->belongsToMany(User::class, 'doctor_chambers')
                    ->withTimestamps();
    }

    // public function services()
    // {
    //     return $this->hasMany(DiagnosticService::class);
    // }

    // public function ratings()
    // {
    //     return $this->hasMany(DiagnosticRating::class);
    // }

    // ✅ Tell Laravel to use uuid instead of id for route binding
    public function getRouteKeyName()
    {
        return 'uuid';
    }

    protected static function booted()
    {
        static::creating(function ($hospital) {
            if (empty($hospital->uuid)) {
                $hospital->uuid = (string) Str::uuid();
            }
        });
    }


    public function chambers()
    {
        return $this->belongsToMany(DoctorChamber::class, 'doctor_chambers')
                    ->withTimestamps();
    }

    public function getLogoImageUrlAttribute()
    {
        return $this->hospital_logo ? asset('storage/'.$this->hospital_logo) : null;
    }

    public function getBannerUrlAttribute()
    {
        $bannerUrl = $this->getRawOriginal('banner_url');

        return $bannerUrl
            ? asset('storage/' . $bannerUrl)
            : null;
    }
}
