<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Department extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'hospital_id',
        'name',
        'code',
        'description',
        'floor',
        'phone_number',
        'email',
        'sort_order',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'boolean',
        ];
    }

    public function hospital(): BelongsTo
    {
        return $this->belongsTo(Hospital::class);
    }

    public function doctors(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'department_doctor'
        )->withPivot([
            'room_number',
            'extension_number',
            'is_department_head',
            'status',
        ])->withTimestamps();
    }
}
