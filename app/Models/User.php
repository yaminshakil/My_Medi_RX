<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens;

    use HasFactory;
    use HasRoles;
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'name',
        'email',
        'mobile',
        'password',
        'status',
        'google_id',
    ];

    protected $guard_name = 'web';

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }

    protected $appends = ['profile_image_url', 'avatar', 'average_rating', 'review_count', 'rating_distribution'];

    // ✅ Tell Laravel to use uuid instead of id for route binding
    public function getRouteKeyName()
    {
        return 'uuid';
    }

    protected static function booted()
    {
        static::creating(function ($user) {
            if (empty($user->uuid)) {
                $user->uuid = (string) Str::uuid();
            }
        });

        /**
         * Automatically set the `name` when first_name or last_name changes
         */
        static::saving(function ($user) {
            if ($user->first_name || $user->last_name) {
                $user->name = trim($user->first_name.' '.$user->last_name);
            }
        });
    }

    public function profileImage()
    {
        return $this->morphOne(Image::class, 'imageable');
    }

    public function firebaseDeviceTokens(): HasMany
    {
        return $this->hasMany(FirebaseDeviceToken::class);
    }

    public function getProfileImageUrlAttribute()
    {
        return $this->profileImage
            ? asset('storage/'.$this->profileImage->path)
            : null;
    }

    public function getAvatarAttribute()
    {
        return $this->profile_image_url;
    }

    public function getIsActiveAttribute(): bool
    {
        return $this->status === 'active';
    }

    public function getIsPatientAttribute(): bool
    {
        return $this->hasRole('Patient');
    }

    public function getIsAdminAttribute(): bool
    {
        return $this->hasRole('Admin');
    }

    public function getIsInactiveAttribute(): bool
    {
        return $this->status === 'inactive';
    }

    public function pharmacy()
    {
        return $this->hasOne(Pharmacy::class);
    }

    public function doctor()
    {
        return $this->hasOne(Doctor::class);
    }

    public function specialties()
    {
        return $this->belongsToMany(MedicalSpecialty::class, 'doctor_specialty', 'doctor_id', 'medical_specialty_id')->withTimestamps();
    }

    public function medicine_doses()
    {
        return $this->hasMany(MedicineDose::class, 'doctor_id');
    }

    public function medicine_durations()
    {
        return $this->hasMany(MedicineDuration::class, 'doctor_id');
    }

    public function hospitals(): BelongsToMany
    {
        return $this->belongsToMany(
            Hospital::class,
            'doctor_chambers',
            'user_id',
            'hospital_id'
        )->withTimestamps();
    }

    public function departments(): BelongsToMany
    {
        return $this->belongsToMany(
            Department::class,
            'department_doctor'
        )->withPivot([
            'room_number',
            'extension_number',
            'is_department_head',
            'status',
        ])->withTimestamps();
    }

    public function hospital()
    {
        return $this->hasOne(Hospital::class, 'created_by', 'id');
    }

    public function chambers()
    {
        return $this->hasMany(DoctorChamber::class, 'doctor_id');
    }

    public function chamber()
    {
        return $this->hasOne(DoctorChamber::class, 'doctor_id', 'id')->where('is_active', true);
    }

    public function patient()
    {
        return $this->hasOne(Patient::class, 'user_id');
    }

    public function reviews()
    {
        return $this->morphMany(Review::class, 'reviewable');
    }

    public function averageRating(): Attribute
    {
        return Attribute::make(
            get: fn () => round($this->reviews()->avg('rating'), 1)
        );
    }

    public function reviewCount(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->reviews()->count()
        );
    }

    public function getRatingDistributionAttribute()
    {
        $ratingPercentages = [];

        $ratingCounts = $this->reviews()
            ->selectRaw('rating, COUNT(*) as count')
            ->groupBy('rating')
            ->pluck('count', 'rating')
            ->toArray();

        $totalReviews = $this->review_count;

        foreach (range(1, 5) as $i) {
            $count = $ratingCounts[$i] ?? 0;
            $ratingPercentages[$i] = $totalReviews > 0
                ? round(($count / $totalReviews) * 100)
                : 0;
        }

        return $ratingPercentages;
    }

    public function pregnancyProfile()
    {
        return $this->hasOne(PregnancyProfile::class);
    }

    public function menstrualCycleProfile(): HasOne
    {
        return $this->hasOne(MenstrualCycleProfile::class);
    }
}
