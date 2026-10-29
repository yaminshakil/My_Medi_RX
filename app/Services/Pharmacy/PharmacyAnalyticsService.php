<?php

namespace App\Services\Pharmacy;

use App\Models\Pharmacist;
use App\Models\Pharmacy;
use App\Models\PharmacyAddress;
use App\Models\PharmacyDispense;
use App\Models\PharmacyInventoryItem;
use App\Models\PharmacyLicense;
use App\Models\PharmacyPrescriptionOrder;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use LogicException;

class PharmacyAnalyticsService
{
    /**
     * @return array{
     *     summary: array<string, array<string, int>|int>,
     *     registrationTrend: array<int, array{month: string, registrations: int}>,
     *     licenseStatuses: array<int, array{status: string, total: int}>,
     *     pharmacistStatuses: array<int, array{status: string, total: int}>,
     *     locations: array<int, array{district: string, division: string|null, total: int}>,
     *     recentPharmacies: array<int, array<string, mixed>>,
     *     audience: string,
     *     operations: array<string, mixed>|null
     * }
     */
    public function dashboard(?User $owner = null): array
    {
        $pharmacyStatuses = $this->statusCounts(
            Pharmacy::query()
                ->when($owner, fn ($query) => $query->where('user_id', $owner->id))
                ->selectRaw('status, COUNT(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status'),
            ['draft', 'pending', 'approved', 'rejected', 'suspended'],
        );
        $licenseStatuses = $this->statusCounts(
            PharmacyLicense::query()
                ->whereHas('pharmacy', fn ($query) => $query->when(
                    $owner,
                    fn ($pharmacyQuery) => $pharmacyQuery->where('user_id', $owner->id),
                ))
                ->selectRaw('status, COUNT(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status'),
            ['pending', 'verified', 'rejected', 'expired'],
        );
        $pharmacistStatuses = $this->statusCounts(
            Pharmacist::query()
                ->whereHas('pharmacy', fn ($query) => $query->when(
                    $owner,
                    fn ($pharmacyQuery) => $pharmacyQuery->where('user_id', $owner->id),
                ))
                ->selectRaw('status, COUNT(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status'),
            ['pending', 'verified', 'rejected'],
        );

        return [
            'summary' => [
                'pharmacies' => [
                    'total'     => array_sum($pharmacyStatuses),
                    'draft'     => $pharmacyStatuses['draft'],
                    'pending'   => $pharmacyStatuses['pending'],
                    'approved'  => $pharmacyStatuses['approved'],
                    'rejected'  => $pharmacyStatuses['rejected'],
                    'suspended' => $pharmacyStatuses['suspended'],
                ],
                'licenses' => [
                    'total'    => array_sum($licenseStatuses),
                    'pending'  => $licenseStatuses['pending'],
                    'verified' => $licenseStatuses['verified'],
                    'rejected' => $licenseStatuses['rejected'],
                    'expired'  => $licenseStatuses['expired'],
                ],
                'pharmacists' => [
                    'total'    => array_sum($pharmacistStatuses),
                    'pending'  => $pharmacistStatuses['pending'],
                    'verified' => $pharmacistStatuses['verified'],
                    'rejected' => $pharmacistStatuses['rejected'],
                ],
                'pendingReviews' => $pharmacyStatuses['pending']
                    + $licenseStatuses['pending']
                    + $pharmacistStatuses['pending'],
            ],
            'registrationTrend'  => $this->registrationTrend($owner),
            'licenseStatuses'    => $this->statusChart($licenseStatuses),
            'pharmacistStatuses' => $this->statusChart($pharmacistStatuses),
            'locations'          => $this->topLocations($owner),
            'recentPharmacies'   => $this->recentPharmacies($owner),
            'audience'           => $owner === null ? 'admin' : 'pharmacy',
            'operations'         => $owner === null
                ? null
                : $this->operations($owner->pharmacy()->first()),
        ];
    }

    /**
     * @param  Collection<string, mixed>  $counts
     * @param  list<string>  $statuses
     * @return array<string, int>
     */
    private function statusCounts(Collection $counts, array $statuses): array
    {
        $normalized = [];

        foreach ($statuses as $status) {
            $normalized[$status] = (int) $counts->get($status, 0);
        }

        return $normalized;
    }

    /**
     * @param  array<string, int>  $counts
     * @return array<int, array{status: string, total: int}>
     */
    private function statusChart(array $counts): array
    {
        $chart = [];

        foreach ($counts as $status => $total) {
            $chart[] = ['status' => $status, 'total' => $total];
        }

        return $chart;
    }

    /**
     * @return array<int, array{month: string, registrations: int}>
     */
    private function registrationTrend(?User $owner): array
    {
        $monthExpression = match (DB::getDriverName()) {
            'sqlite' => "strftime('%Y-%m', created_at)",
            'pgsql'  => "TO_CHAR(created_at, 'YYYY-MM')",
            'mysql'  => "DATE_FORMAT(created_at, '%Y-%m')",
            default  => throw new LogicException('Pharmacy analytics does not support this database driver.'),
        };
        $startOfWindow = now()->startOfMonth()->subMonths(5);
        $registrationsByMonth = Pharmacy::query()
            ->when($owner, fn ($query) => $query->where('user_id', $owner->id))
            ->whereBetween('created_at', [$startOfWindow, now()->endOfMonth()])
            ->selectRaw("{$monthExpression} as month, COUNT(*) as total")
            ->groupByRaw($monthExpression)
            ->pluck('total', 'month');

        $trend = [];

        for ($offset = 0; $offset < 6; $offset++) {
            $month = $startOfWindow->copy()->addMonths($offset);
            $trend[] = [
                'month'         => $month->format('M Y'),
                'registrations' => (int) ($registrationsByMonth[$month->format('Y-m')] ?? 0),
            ];
        }

        return $trend;
    }

    /**
     * @return array<int, array{district: string, division: string|null, total: int}>
     */
    private function topLocations(?User $owner): array
    {
        return PharmacyAddress::query()
            ->whereHas('pharmacy', fn ($query) => $query->when(
                $owner,
                fn ($pharmacyQuery) => $pharmacyQuery->where('user_id', $owner->id),
            ))
            ->whereNotNull('district')
            ->where('district', '<>', '')
            ->select('district', 'division')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('district', 'division')
            ->orderByDesc('total')
            ->orderBy('district')
            ->limit(6)
            ->get()
            ->map(fn (PharmacyAddress $address): array => [
                'district' => $address->district,
                'division' => $address->division,
                'total'    => (int) $address->total,
            ])
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function recentPharmacies(?User $owner): array
    {
        return Pharmacy::query()
            ->when($owner, fn ($query) => $query->where('user_id', $owner->id))
            ->select(['id', 'user_id', 'name', 'status', 'created_at'])
            ->with([
                'owner:id,name,email',
                'address:id,pharmacy_id,district,division',
            ])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit(6)
            ->get()
            ->map(fn (Pharmacy $pharmacy): array => [
                'id'         => $pharmacy->id,
                'name'       => $pharmacy->name,
                'status'     => $pharmacy->status,
                'created_at' => $pharmacy->created_at?->toISOString(),
                'owner'      => $pharmacy->owner?->name,
                'district'   => $pharmacy->address?->district,
                'division'   => $pharmacy->address?->division,
            ])
            ->all();
    }

    /**
     * @return array{
     *     todayPrescriptions: int,
     *     pendingPrescriptions: int,
     *     medicinesDispensedToday: int,
     *     lowStockDrugs: int,
     *     expiringSoon: int,
     *     lowStockItems: array<int, array<string, mixed>>,
     *     expiringSoonItems: array<int, array<string, mixed>>,
     *     recentPrescriptions: array<int, array<string, mixed>>
     * }
     */
    private function operations(?Pharmacy $pharmacy): array
    {
        if ($pharmacy === null) {
            return [
                'todayPrescriptions'      => 0,
                'pendingPrescriptions'    => 0,
                'medicinesDispensedToday' => 0,
                'lowStockDrugs'           => 0,
                'expiringSoon'            => 0,
                'lowStockItems'           => [],
                'expiringSoonItems'       => [],
                'recentPrescriptions'     => [],
            ];
        }

        $inventory = $pharmacy->inventoryItems();
        $prescriptionOrders = $pharmacy->prescriptionOrders();
        $today = today();

        return [
            'todayPrescriptions' => (clone $prescriptionOrders)
                ->whereDate('received_at', $today)
                ->count(),
            'pendingPrescriptions' => (clone $prescriptionOrders)
                ->whereIn('status', ['received', 'processing'])
                ->count(),
            'medicinesDispensedToday' => (int) PharmacyDispense::query()
                ->where('pharmacy_id', $pharmacy->id)
                ->whereDate('dispensed_at', $today)
                ->sum('quantity'),
            'lowStockDrugs' => (clone $inventory)
                ->whereColumn('stock_quantity', '<=', 'reorder_level')
                ->count(),
            'expiringSoon' => (clone $inventory)
                ->where('stock_quantity', '>', 0)
                ->whereBetween('expiry_date', [$today, $today->copy()->addDays(30)])
                ->count(),
            'lowStockItems' => (clone $inventory)
                ->whereColumn('stock_quantity', '<=', 'reorder_level')
                ->orderBy('stock_quantity')
                ->limit(5)
                ->get()
                ->map(fn (PharmacyInventoryItem $item): array => [
                    'id'             => $item->id,
                    'medicine_name'  => $item->medicine_name,
                    'strength'       => $item->strength,
                    'stock_quantity' => $item->stock_quantity,
                    'reorder_level'  => $item->reorder_level,
                ])
                ->all(),
            'expiringSoonItems' => (clone $inventory)
                ->where('stock_quantity', '>', 0)
                ->whereBetween('expiry_date', [$today, $today->copy()->addDays(30)])
                ->orderBy('expiry_date')
                ->limit(5)
                ->get()
                ->map(fn (PharmacyInventoryItem $item): array => [
                    'id'             => $item->id,
                    'medicine_name'  => $item->medicine_name,
                    'strength'       => $item->strength,
                    'stock_quantity' => $item->stock_quantity,
                    'expiry_date'    => $item->expiry_date?->toDateString(),
                ])
                ->all(),
            'recentPrescriptions' => (clone $prescriptionOrders)
                ->with('prescription:id,prescription_number')
                ->orderByDesc('received_at')
                ->limit(5)
                ->get()
                ->map(fn (PharmacyPrescriptionOrder $order): array => [
                    'id'                  => $order->id,
                    'prescription_number' => $order->prescription?->prescription_number,
                    'status'              => $order->status,
                    'received_at'         => $order->received_at?->toISOString(),
                ])
                ->all(),
        ];
    }
}
