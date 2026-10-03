<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use App\Traits\HasTranslations;

class MedicalSpecialty extends Model
{
    use HasFactory;
    use HasTranslations;

    protected $fillable = ['name', 'icon', 'description', 'parent_id', 'is_surgical'];

    protected $appends = [
    'translated_name',
    'translated_description'
];

    public function getTranslatedNameAttribute(): ?string
    {
        return $this->translated('name')
            ?? $this->name;
    }

    public function getTranslatedDescriptionAttribute(): ?string
    {
        return $this->translated('description')
            ?? $this->description;
    }

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

    public function translations(): MorphMany
    {
        return $this->morphMany(
            Translation::class,
            'translatable'
        );
    }
}
