<?php

namespace App\Modules\BloodBank\Services;

use App\Modules\BloodBank\Enums\BloodComponent;
use App\Modules\BloodBank\Enums\BloodGroup;
use App\Modules\BloodBank\Repositories\Contracts\BloodBagRepositoryInterface;
use App\Modules\BloodBank\Repositories\Contracts\BloodIssueRepositoryInterface;
use App\Modules\BloodBank\Repositories\Contracts\EmergencyRequestRepositoryInterface;
use App\Modules\BloodBank\Support\LookupOptions;
use Illuminate\Support\Collection;

/** Read-side logic for the live stock board: matrix, totals, headline numbers. */
class BloodStockService
{
    public function __construct(
        private readonly BloodBagRepositoryInterface $bags,
        private readonly BloodIssueRepositoryInterface $issues,
        private readonly EmergencyRequestRepositoryInterface $requests,
    ) {
    }

    /** Every group x component cell, including zeros, so the grid never has holes. */
    public function overview(): array
    {
        $rows = $this->bags->stockMatrix()
            ->keyBy(fn ($row) => $row->blood_group->value.'|'.$row->component->value);

        $matrix = [];
        $byGroup = [];
        $byComponent = [];
        $grand = 0;

        foreach (BloodGroup::cases() as $group) {
            foreach (BloodComponent::cases() as $component) {
                $row   = $rows->get($group->value.'|'.$component->value);
                $units = (int) ($row->units ?? 0);
                $limit = config("blood-bank.stock_thresholds.{$component->value}");

                $matrix[$group->value][$component->value] = [
                    'units'     => $units,
                    'volume_ml' => (int) ($row->volume_ml ?? 0),
                    'level'     => $units <= $limit['critical'] ? 'critical' : ($units <= $limit['low'] ? 'low' : 'ok'),
                    // 0..1 height of the "bag fill" bar: full at twice the low-stock threshold.
                    'fill'      => round(min(1, $units / max(1, $limit['low'] * 2)), 2),
                ];

                $byGroup[$group->value]         = ($byGroup[$group->value] ?? 0) + $units;
                $byComponent[$component->value] = ($byComponent[$component->value] ?? 0) + $units;
                $grand += $units;
            }
        }

        return [
            'groups'     => BloodGroup::values(),
            'components' => LookupOptions::components(),
            'matrix'     => $matrix,
            'totals'     => ['all' => $grand, 'by_group' => $byGroup, 'by_component' => $byComponent],
        ];
    }

    /** Units inside their component-specific alert window. */
    public function expiringSoon(int $limit = 50): Collection
    {
        return $this->bags->expiringSoon(config('blood-bank.expiry_alert_days'), $limit);
    }

    public function summary(): array
    {
        return [
            'available_units' => $this->bags->countAvailable(),
            'expiring_count'  => $this->expiringSoon(500)->count(),
            'open_requests'   => $this->requests->countOpen(),
            'issued_today'    => $this->issues->countSince(today()),
        ];
    }
}
