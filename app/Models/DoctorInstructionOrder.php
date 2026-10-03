<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DoctorInstructionOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'doctor_id',
        'instruction_id',
        'sort_order',
    ];
}
