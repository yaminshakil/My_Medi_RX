<?php

namespace App\Modules\BloodBank\Enums;

enum BloodGroup: string
{
    // Order matters: it is the order of the rows on the stock matrix.
    case APositive = 'A+';
    case BPositive = 'B+';
    case ABPositive = 'AB+';
    case OPositive = 'O+';
    case ANegative = 'A-';
    case BNegative = 'B-';
    case ABNegative = 'AB-';
    case ONegative = 'O-';

    /** ABO part only: A, B, AB or O. */
    public function abo(): string
    {
        return rtrim($this->value, '+-');
    }

    public function isRhPositive(): bool
    {
        return str_ends_with($this->value, '+');
    }

    /** @return string[] */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
