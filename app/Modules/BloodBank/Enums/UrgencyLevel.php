<?php

namespace App\Modules\BloodBank\Enums;

enum UrgencyLevel: string
{
    case Critical = 'critical';
    case Urgent   = 'urgent';
    case Standard = 'standard';

    public function label(): string
    {
        return ucfirst($this->value);
    }
}
