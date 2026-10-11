<?php

namespace App\Modules\BloodBank\Models;

use App\Modules\BloodBank\Enums\BloodGroup;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Patient extends Model
{
    protected $table = 'bb_patients';

    protected $fillable = ['mrn', 'clinical_file_no', 'name', 'date_of_birth', 'gender', 'blood_group'];

    protected function casts(): array
    {
        return ['blood_group' => BloodGroup::class, 'date_of_birth' => 'date'];
    }

    public function issues(): HasMany
    {
        return $this->hasMany(BloodIssue::class);
    }
}
