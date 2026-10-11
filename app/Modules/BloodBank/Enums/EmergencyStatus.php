<?php

namespace App\Modules\BloodBank\Enums;

enum EmergencyStatus: string
{
    case Open      = 'open';
    case Fulfilled = 'fulfilled';
    case Cancelled = 'cancelled';
    case Expired   = 'expired';

    public function label(): string
    {
        return ucfirst($this->value);
    }
}
