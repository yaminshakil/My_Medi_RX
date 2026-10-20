<?php

namespace App\Services\Patients;

use App\Models\MedicationLog;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class MedicationDayDetailsService
{
    public function getDayDetails(
        int $patientId,
        string $date
    ): array {
        $selectedDate = Carbon::parse($date);

        $logs = MedicationLog::query()
            ->where('patient_id', $patientId)
            ->whereDate('scheduled_at', $selectedDate->toDateString())
            ->with([
                'patientMedication.prescriptionMedicine.medicines',
            ])
            ->orderBy('scheduled_at')
            ->get();

        $items = $logs->map(function ($log) {
            $prescriptionMedicine = $log->patientMedication
                ?->prescriptionMedicine;

            $medicine = $prescriptionMedicine?->medicines;

            $status = $this->formatStatus(
                $log->status,
                $log->scheduled_at
            );

            return [
                'name' => $medicine?->brand_name ?? 'Unknown medication',
                'dosage' => $prescriptionMedicine?->strength ?? '',
                'time' => Carbon::parse($log->scheduled_at)
                    ->format('h:i A'),
                'isTaken' => strtolower($log->status) === 'taken',
                'status' => $status,
            ];
        });

        return [
            'date' => $selectedDate->toDateString(),
            'formattedDate' => $selectedDate->format('F d, Y'),
            'items' => $items->values()->all(),
        ];
    }

    private function formatStatus(
        ?string $status,
        mixed $scheduledAt
    ): string {
        if (strtolower($status ?? '') === 'taken') {
            return 'Taken';
        }

        if (strtolower($status ?? '') === 'missed') {
            return 'Missed';
        }

        if (strtolower($status ?? '') === 'skipped') {
            return 'Skipped';
        }

        if (Carbon::parse($scheduledAt)->isFuture()) {
            return 'Upcoming';
        }

        return 'Pending';
    }
}
