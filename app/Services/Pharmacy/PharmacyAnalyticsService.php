<?php

namespace App\Services\Pharmacy;

use App\Models\Pharmacist;
use App\Models\Pharmacy;
use App\Models\PharmacyAddress;
use App\Models\PharmacyLicense;
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
     *     recentPharmacies: array<int, array<string, mixed>>
     * }
     */
    public function dashboard(): array
    {
        $pharmacyStatuses = $this->statusCounts(
            Pharmacy::query()
                ->selectRaw('status, COUNT(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status'),
            ['draft', 'pending', 'approved', 'rejected', 'suspended'],
        );
        $licenseStatuses = $this->statusCounts(
            PharmacyLicense::query()
                ->selectRaw('status, COUNT(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status'),
            ['pending', 'verified', 'rejected', 'expired'],
        );
        $pharmacistStatuses = $this->statusCounts(
            Pharmacist::query()
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
            'registrationTrend'  => $this->registrationTrend(),
            'licenseStatuses'    => $this->statusChart($licenseStatuses),
            'pharmacistStatuses' => $this->statusChart($pharmacistStatuses),
            'locations'          => $this->topLocations(),
            'recentPharmacies'   => $this->recentPharmacies(),
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
    private function registrationTrend(): array
    {
        $monthExpression = match (DB::getDriverName()) {
            'sqlite' => "strftime('%Y-%m', created_at)",
            'pgsql'  => "TO_CHAR(created_at, 'YYYY-MM')",
            'mysql'  => "DATE_FORMAT(created_at, '%Y-%m')",
            default  => throw new LogicException('Pharmacy analytics does not support this database driver.'),
        };
        $startOfWindow = now()->startOfMonth()->subMonths(5);
        $registrationsByMonth = Pharmacy::query()
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
    private function topLocations(): array
    {
        return PharmacyAddress::query()
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
    private function recentPharmacies(): array
    {
        return Pharmacy::query()
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
}
