<?php

namespace App\Modules\BloodBank\Models;

use App\Models\Patient as AppPatient;
use App\Modules\BloodBank\Enums\BloodGroup;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** The blood bank's view of the application's own patients table. */
class Patient extends AppPatient
{
    protected $table = 'patients';

    // Your table has no MRN or clinical file columns, so patient_number serves as both.
    protected function mrn(): Attribute
    {
        return Attribute::get(fn () => $this->patient_number);
    }

    protected function clinicalFileNo(): Attribute
    {
        return Attribute::get(fn () => $this->patient_number);
    }

    // Your column is a plain string; the module works with the BloodGroup enum.
    // Tolerates 'a+', 'A Positive' and similar. Anything it cannot read becomes null.
    protected function bloodGroup(): Attribute
    {
        return Attribute::get(function ($value) {
            if (blank($value)) {
                return null;
            }

            $v = strtoupper(preg_replace('/\s+/', '', $value));
            $v = str_replace(['POSITIVE', 'NEGATIVE'], ['+', '-'], $v);

            return BloodGroup::tryFrom($v);
        });
    }

    public function issues(): HasMany
    {
        return $this->hasMany(BloodIssue::class, 'patient_id');
    }
}
