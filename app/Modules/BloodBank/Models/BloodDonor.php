<?php

namespace App\Modules\BloodBank\Models;

use App\Modules\BloodBank\Enums\BloodGroup;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class BloodDonor extends Model
{
    use Notifiable;

    protected $table = 'bb_donors';

    protected $fillable = [
        'user_id', 'name', 'phone', 'email', 'blood_group', 'latitude', 'longitude',
        'city', 'last_donated_at', 'is_verified', 'verified_at', 'is_available',
    ];

    protected function casts(): array
    {
        return [
            'blood_group'     => BloodGroup::class,
            'latitude'        => 'float',
            'longitude'       => 'float',
            'last_donated_at' => 'date',
            'verified_at'     => 'datetime',
            'is_verified'     => 'boolean',
            'is_available'    => 'boolean',
        ];
    }

    public function routeNotificationForMail(): ?string
    {
        return $this->email;
    }
}
