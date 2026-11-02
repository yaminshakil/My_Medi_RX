<?php

namespace App\Modules\BloodBank\Enums;

enum StorageUnitType: string
{
    case Refrigerator = 'refrigerator';
    case Freezer = 'freezer';
    case PlateletAgitator = 'platelet_agitator';

    public function label(): string
    {
        return match ($this) {
            self::Refrigerator     => 'Refrigerator',
            self::Freezer          => 'Freezer',
            self::PlateletAgitator => 'Platelet agitator',
        };
    }

    /** @return string[] */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
