<?php

namespace App\Services;

use App\Models\MedicationSchedule;
use App\Models\MedicationLog;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class MedicationLogGenerator
{
    public function generate(
        MedicationSchedule $schedule,
        string $from,
        string $to
    ): void {
        $start = Carbon::parse($from)->startOfDay();
        $end = Carbon::parse($to)->startOfDay();

        if ($schedule->start_date) {
            $start = $start->max(
                Carbon::parse($schedule->start_date)->startOfDay()
            );
        }

        if ($schedule->end_date) {
            $end = $end->min(
                Carbon::parse($schedule->end_date)->startOfDay()
            );
        }

        if ($start->gt($end)) {
            return;
        }

        $daysOfWeek = $schedule->days_of_week;

        if (is_string($daysOfWeek)) {
            $daysOfWeek = json_decode($daysOfWeek, true) ?? [];
        }

        foreach (CarbonPeriod::create($start, $end) as $date) {
            // If days_of_week is configured, use ISO weekdays:
            // Monday = 1 ... Sunday = 7.
            if (
                !empty($daysOfWeek) &&
                !in_array($date->dayOfWeekIso, $daysOfWeek)
            ) {
                continue;
            }

            $scheduledAt = $date->copy()->setTimeFromTimeString(
                $schedule->scheduled_time
            );

            MedicationLog::firstOrCreate(
                [
                    'medication_schedule_id' => $schedule->id,
                    'scheduled_at' => $scheduledAt,
                ],
                [
                    'patient_id' => $schedule->medication->patient_id,
                    'patient_medication_id' =>
                        $schedule->patient_medication_id,
                    'scheduled_date' => $date->toDateString(),
                    'status' => 'upcoming',
                ]
            );
        }
    }
}
