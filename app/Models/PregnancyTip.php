<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PregnancyTip extends Model
{
    protected $fillable = [
        'title',
        'description',
        'icon',
        'color_hex',
        'trimester',
        'week_start',
        'week_end',
        'sort_order',
        'is_published',
        'source_reference',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'trimester' => 'integer',
            'week_start' => 'integer',
            'week_end' => 'integer',
        ];
    }
}
