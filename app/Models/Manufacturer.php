<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Manufacturer extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_name',
        'is_active',
        'created_by',
        'updated_by',
        'doctor_id',
    ];

    public function medicine()
    {
        return $this->hasMany(Medicine::class);
    }
}
