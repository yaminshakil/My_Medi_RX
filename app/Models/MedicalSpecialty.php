<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicalSpecialty extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'icon', 'description', 'parent_id', 'is_surgical'];

    public function children()
    {
        return $this->hasMany(MedicalSpecialty::class, 'parent_id');
    }

    public function parent()
    {
        return $this->belongsTo(MedicalSpecialty::class, 'parent_id');
    }

    public function doctors()
    {
        return $this->belongsToMany(User::class, 'doctor_specialty', 'medical_specialty_id', 'doctor_id')->withTimestamps();
    }
}
