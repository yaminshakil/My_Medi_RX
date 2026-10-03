<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasTranslations;

class Medicine extends Model
{
    use HasFactory;
    use HasTranslations;

    protected $fillable = [
        'brand_name',
        'generic_name',
        'strength',
        'type',
        'is_active',
        'manufacturer_id',
        'created_by',
        'updated_by',
        'doctor_id',
    ];

    public function manufacturer()
    {
        return $this->belongsTo(Manufacturer::class);
    }
}
