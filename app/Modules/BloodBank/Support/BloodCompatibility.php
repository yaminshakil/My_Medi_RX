<?php

namespace App\Modules\BloodBank\Support;

use App\Modules\BloodBank\Enums\BloodComponent;
use App\Modules\BloodBank\Enums\BloodGroup;

/**
 * Donor -> recipient compatibility.
 *
 * Standard ABO/Rh rules. Hospitals differ on platelet policy, so treat the platelet rule
 * (plasma-style ABO + strict Rh) as a safe default and have your medical director confirm it.
 * Staff can still issue an "incompatible" unit, but only with a recorded override reason.
 */
final class BloodCompatibility
{
    // Red cells / whole blood: donor ABO => recipient ABO groups that can receive it.
    private const RED_CELL_ABO = [
        'O'  => ['O', 'A', 'B', 'AB'],
        'A'  => ['A', 'AB'],
        'B'  => ['B', 'AB'],
        'AB' => ['AB'],
    ];

    // Plasma / platelets: the antibodies travel with the product, so the direction reverses.
    private const PLASMA_ABO = [
        'AB' => ['AB', 'A', 'B', 'O'],
        'A'  => ['A', 'O'],
        'B'  => ['B', 'O'],
        'O'  => ['O'],
    ];

    public static function isCompatible(BloodGroup $donor, BloodGroup $recipient, BloodComponent $component): bool
    {
        if ($component->containsRedCells()) {
            return in_array($recipient->abo(), self::RED_CELL_ABO[$donor->abo()], true)
                && self::rhOk($donor, $recipient);
        }

        $aboOk = in_array($recipient->abo(), self::PLASMA_ABO[$donor->abo()], true);

        // Plasma carries no red cells, so Rh does not matter. Platelets stay Rh-strict.
        return $aboOk && ($component === BloodComponent::Plasma || self::rhOk($donor, $recipient));
    }

    /**
     * Every blood group whose units (or donations) may be given to $recipient.
     *
     * @return BloodGroup[]
     */
    public static function donorGroupsFor(BloodGroup $recipient, BloodComponent $component): array
    {
        return array_values(array_filter(
            BloodGroup::cases(),
            fn (BloodGroup $donor) => self::isCompatible($donor, $recipient, $component)
        ));
    }

    // Rh-positive units must never go to an Rh-negative recipient.
    private static function rhOk(BloodGroup $donor, BloodGroup $recipient): bool
    {
        return ! $donor->isRhPositive() || $recipient->isRhPositive();
    }
}
