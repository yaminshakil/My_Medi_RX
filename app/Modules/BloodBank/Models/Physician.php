<?php

namespace App\Modules\BloodBank\Models;

use Illuminate\Database\Eloquent\Model;

class Physician extends Model
{
    protected $table = 'bb_physicians';

    protected $fillable = ['name', 'license_no', 'department', 'phone', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }
}
