<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PregnancyWeeklyHighlight extends Model
{
    protected $fillable = [
        'week',
        'fruit_name',
        'approx_length_cm',
        'approx_weight_grams',
        'description',
        'source_reference',
        'source_version',
        'is_published',
    ];

    protected function casts(): array
    {
        return [
            'approx_length_cm'    => 'decimal:2',
            'approx_weight_grams' => 'integer',
            'is_published'        => 'boolean',
        ];
    }
}
