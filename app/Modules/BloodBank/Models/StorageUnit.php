<?php

namespace App\Modules\BloodBank\Models;

use App\Models\Hospital as ApplicationHospital;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StorageUnit extends Model
{
    protected $table = 'bb_storage_units';

    protected $fillable = [
        'name',
        'location',
        'type',
        'min_temp_c',
        'max_temp_c',
        'capacity',
        'is_active',
        'hospital_id',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function bags(): HasMany
    {
        return $this->hasMany(BloodBag::class);
    }

    public function hospital(): BelongsTo
    {
        return $this->belongsTo(ApplicationHospital::class);
    }
}
