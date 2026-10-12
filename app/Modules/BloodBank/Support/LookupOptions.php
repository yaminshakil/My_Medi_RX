<?php

namespace App\Modules\BloodBank\Support;

use App\Modules\BloodBank\Enums\BagStatus;
use App\Modules\BloodBank\Enums\BloodComponent;
use App\Modules\BloodBank\Enums\BloodGroup;
use App\Modules\BloodBank\Enums\UrgencyLevel;

/** Dropdown option lists shared by several Inertia pages. */
final class LookupOptions
{
    public static function groups(): array
    {
        return array_map(
            fn (string $group) => [
                'value' => $group,
                'label' => $group,
            ],
            BloodGroup::values()
        );
    }

    public static function components(): array
    {
        return array_map(fn (BloodComponent $c) => [
            'value'           => $c->value,
            'label'           => $c->label(),
            'shelf_life_days' => $c->shelfLifeDays(),
        ], BloodComponent::cases());
    }

    public static function statuses(): array
    {
        return array_map(fn (BagStatus $s) => ['value' => $s->value, 'label' => $s->label()], BagStatus::cases());
    }

    public static function urgencies(): array
    {
        return array_map(fn (UrgencyLevel $u) => ['value' => $u->value, 'label' => $u->label()], UrgencyLevel::cases());
    }
}
