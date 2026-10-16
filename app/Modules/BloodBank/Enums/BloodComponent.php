<?php

namespace App\Modules\BloodBank\Enums;

enum BloodComponent: string
{
    case WholeBlood = 'whole_blood';
    case PackedRedCells = 'packed_red_cells';
    case Platelets = 'platelets';
    case Plasma = 'plasma';

    public function label(): string
    {
        return match ($this) {
            self::WholeBlood     => 'Whole blood',
            self::PackedRedCells => 'Packed red cells',
            self::Platelets      => 'Platelets',
            self::Plasma         => 'Plasma',
        };
    }

    /** Default shelf life used to pre-fill the expiry date from the collection date. */
    public function shelfLifeDays(): int
    {
        return match ($this) {
            self::WholeBlood     => 35,
            self::PackedRedCells => 42,
            self::Platelets      => 5,
            self::Plasma         => 365, // frozen
        };
    }

    /** Red-cell containing products follow red-cell ABO rules; platelets/plasma follow plasma rules. */
    public function containsRedCells(): bool
    {
        return in_array($this, [self::WholeBlood, self::PackedRedCells], true);
    }

    /** @return string[] */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
