<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Translation extends Model
{
    protected $fillable = [
        'translation_key',
        'translatable_type',
        'translatable_id',
        'field',
        'locale',
        'value',
    ];

    public function translatable()
    {
        return $this->morphTo();
    }
}
