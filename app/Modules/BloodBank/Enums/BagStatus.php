<?php

namespace App\Modules\BloodBank\Enums;

enum BagStatus: string
{
    case Available = 'available';
    case Quarantined = 'quarantined'; // collected but waiting for screening results
    case Issued = 'issued';
    case Expired = 'expired';
    case Discarded = 'discarded';

    public function label(): string
    {
        return ucfirst($this->value);
    }
}
