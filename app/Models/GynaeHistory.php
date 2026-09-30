<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GynaeHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'prescription_id',
        // Marriage Details
        'marital_status',
        'marriage_duration',
        'consanguinity',
        // Menstrual History
        'menarche_age',
        'lmp',
        'cycle',
        'flow',
        'dysmenorrhea',
        'contraceptive_use',
        // Obstetrical History
        'gravida',
        'para',
        'abortion',
        'living_children',
        // Current Pregnancy
        'edd',
        'anc',
        // Notes
        'other_history',
    ];

    protected $dates = ['lmp'];

    protected $casts = [
        'dysmenorrhea' => 'boolean',
        'contraceptive_use' => 'boolean',
        'consanguinity' => 'boolean',
    ];

    public function prescription()
    {
        return $this->belongsTo(Prescription::class);
    }
}
