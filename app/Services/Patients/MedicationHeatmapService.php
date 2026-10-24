<?php

namespace App\Services\Patients;

use App\Models\MedicationLog;
use App\Models\Patient;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class MedicationHeatmapService
{
    public function getMonthlyHeatmap(
        Patient $patient,
        int $month,
        int $year
    ): array {
        $startDate = Carbon::create($year, $month, 1)->startOfDay();
        $endDate = $startDate->copy()->endOfMonth();

        // Retrieve the patient's dose logs for the requested month.
        $logs = MedicationLog::query()
            ->where('patient_id', $patient->id)
            ->whereBetween('scheduled_at', [
                $startDate,
                $endDate->copy()->endOfDay(),
            ])
            ->where('scheduled_at', '<=', now())
            ->get([
                'scheduled_at',
                'scheduled_date',
                'status',
            ]);

        $logsByDate = $logs->groupBy(function ($log) {
            return $log->scheduled_date
                ? Carbon::parse($log->scheduled_date)->toDateString()
                : Carbon::parse($log->scheduled_at)->toDateString();
        });

        $heatmap = [];

        foreach (CarbonPeriod::create($startDate, $endDate) as $date) {
            $day = $date->day;
            $dateString = $date->toDateString();

            // Do not count future calendar dates as missed.
            if ($date->isFuture()) {
                $heatmap[] = [
                    'day'         => $day,
                    'date'        => $dateString,
                    'consistency' => null,
                    'status'      => 'no_doses',
                ];

                continue;
            }

            $dayLogs = $logsByDate->get($dateString, collect());
            $today = Carbon::today();

            $todayLogs = MedicationLog::where('patient_id', $patient->id)
                ->whereDate('scheduled_at', $today)
                ->get();

            $total = $todayLogs->count();

            $taken = $dayLogs
                ->where('status', 'taken')
                ->count();

            if ($total === 0) {
                $consistency = null;
                $status = 'no_doses';
            } else {
                $consistency = round($taken / $total, 2);

                if ($taken === $total) {
                    $status = 'all_taken';
                } elseif ($taken === 0 || $consistency <= 0.20) {
                    $status = 'missed';
                } else {
                    $status = 'partially_taken';
                }
            }

            $heatmap[] = [
                'day'         => $day,
                'date'        => $dateString,
                'consistency' => $consistency,
                'status'      => $status,
            ];
        }

        return [
            'month'     => $month,
            'year'      => $year,
            'monthName' => $startDate->format('F Y'),
            'totalDays' => $startDate->daysInMonth,
            'heatmap'   => $heatmap,
            'total'     => $total,
        ];
    }
}
