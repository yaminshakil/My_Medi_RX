<?php

namespace App\Services\Doctors;

use App\Models\Prescription;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;

class DoctorAnalyticsService
{
    /**
     * @return array{
     *     daily: array{patients: int, prescriptions: int, new_visits: int, follow_up_visits: int},
     *     monthly: array{patients: int, prescriptions: int, new_visits: int, follow_up_visits: int},
     *     adherence: array{period_days: int, eligible_doses: int, taken: int, missed: int, skipped: int, percentage: float|null},
     *     patient_adherence: array<int, array{patient_id: int, patient_name: string, patient_number: string, eligible_doses: int, taken: int, missed: int, percentage: float}>,
     *     recent_prescriptions: Collection<int, Prescription>
     * }
     */
    public function dashboard(User $doctor): array
    {
        $now = Date::now();
        $adherence = $this->adherenceSummary($doctor);

        return [
            'daily'                => $this->prescriptionSummary($doctor, $now->copy()->startOfDay(), $now),
            'monthly'              => $this->prescriptionSummary($doctor, $now->copy()->startOfMonth(), $now),
            'adherence'            => $adherence,
            'patient_adherence'    => $this->patientAdherence($doctor),
            'recent_prescriptions' => $this->prescriptionLogsQuery($doctor)
                ->with([
                    'patient:id,name,patient_number',
                    'medications.medicine:id,brand_name,generic_name,strength',
                ])
                ->limit(10)
                ->get([
                    'id',
                    'patient_id',
                    'prescription_number',
                    'diagnosis',
                    'is_followup',
                    'status',
                    'created_at',
                ]),
        ];
    }

    /**
     * @return Builder<Prescription>
     */
    public function prescriptionLogsQuery(User $doctor): Builder
    {
        return Prescription::query()
            ->where('doctor_id', $doctor->id)
            ->orderByDesc('created_at')
            ->orderByDesc('id');
    }

    /**
     * @return array{patients: int, prescriptions: int, new_visits: int, follow_up_visits: int}
     */
    private function prescriptionSummary(User $doctor, CarbonInterface $from, CarbonInterface $to): array
    {
        $summary = $this->prescriptionLogsQuery($doctor)
            ->where('status', 'issued')
            ->whereBetween('created_at', [$from, $to])
            ->selectRaw('COUNT(DISTINCT patient_id) AS patients')
            ->selectRaw('COUNT(*) AS prescriptions')
            ->selectRaw('SUM(CASE WHEN is_followup = 0 THEN 1 ELSE 0 END) AS new_visits')
            ->selectRaw('SUM(CASE WHEN is_followup = 1 THEN 1 ELSE 0 END) AS follow_up_visits')
            ->first();

        return [
            'patients'         => (int) $summary->patients,
            'prescriptions'    => (int) $summary->prescriptions,
            'new_visits'       => (int) $summary->new_visits,
            'follow_up_visits' => (int) $summary->follow_up_visits,
        ];
    }

    /**
     * @return array{period_days: int, eligible_doses: int, taken: int, missed: int, skipped: int, percentage: float|null}
     */
    private function adherenceSummary(User $doctor): array
    {
        $summary = $this->adherenceLogsQuery($doctor)
            ->selectRaw("SUM(CASE WHEN medication_logs.status = 'taken' THEN 1 ELSE 0 END) AS taken")
            ->selectRaw("SUM(CASE WHEN medication_logs.status = 'missed' THEN 1 ELSE 0 END) AS missed")
            ->selectRaw("SUM(CASE WHEN medication_logs.status = 'skipped' THEN 1 ELSE 0 END) AS skipped")
            ->first();
        $taken = (int) $summary->taken;
        $missed = (int) $summary->missed;
        $eligibleDoses = $taken + $missed;

        return [
            'period_days'    => 30,
            'eligible_doses' => $eligibleDoses,
            'taken'          => $taken,
            'missed'         => $missed,
            'skipped'        => (int) $summary->skipped,
            'percentage'     => $eligibleDoses > 0 ? round(($taken / $eligibleDoses) * 100, 2) : null,
        ];
    }

    /**
     * @return array<int, array{patient_id: int, patient_name: string, patient_number: string, eligible_doses: int, taken: int, missed: int, percentage: float}>
     */
    private function patientAdherence(User $doctor): array
    {
        return $this->adherenceLogsQuery($doctor)
            ->select([
                'patients.id as patient_id',
                'patients.name as patient_name',
                'patients.patient_number',
            ])
            ->selectRaw("SUM(CASE WHEN medication_logs.status = 'taken' THEN 1 ELSE 0 END) AS taken")
            ->selectRaw("SUM(CASE WHEN medication_logs.status = 'missed' THEN 1 ELSE 0 END) AS missed")
            ->groupBy('patients.id', 'patients.name', 'patients.patient_number')
            ->havingRaw("SUM(CASE WHEN medication_logs.status IN ('taken', 'missed') THEN 1 ELSE 0 END) > 0")
            ->orderByRaw("SUM(CASE WHEN medication_logs.status = 'missed' THEN 1 ELSE 0 END) DESC")
            ->orderBy('patients.name')
            ->limit(10)
            ->get()
            ->map(static function (object $patient): array {
                $taken = (int) $patient->taken;
                $missed = (int) $patient->missed;
                $eligibleDoses = $taken + $missed;

                return [
                    'patient_id'     => (int) $patient->patient_id,
                    'patient_name'   => $patient->patient_name,
                    'patient_number' => $patient->patient_number,
                    'eligible_doses' => $eligibleDoses,
                    'taken'          => $taken,
                    'missed'         => $missed,
                    'percentage'     => round(($taken / $eligibleDoses) * 100, 2),
                ];
            })
            ->all();
    }

    private function adherenceLogsQuery(User $doctor): \Illuminate\Database\Query\Builder
    {
        return DB::table('medication_logs')
            ->join('patient_medications', 'patient_medications.id', '=', 'medication_logs.patient_medication_id')
            ->join('prescription_medicines', 'prescription_medicines.id', '=', 'patient_medications.prescription_medicine_id')
            ->join('prescriptions', 'prescriptions.id', '=', 'prescription_medicines.prescription_id')
            ->join('patients', 'patients.id', '=', 'medication_logs.patient_id')
            ->where('prescriptions.doctor_id', $doctor->id)
            ->whereIn('prescriptions.status', ['issued', 'completed'])
            ->whereNull('prescriptions.deleted_at')
            ->whereNull('patient_medications.deleted_at')
            ->whereNull('patients.deleted_at')
            ->whereBetween('medication_logs.scheduled_at', [
                Date::now()->subDays(29)->startOfDay(),
                Date::now(),
            ])
            ->whereIn('medication_logs.status', ['taken', 'missed', 'skipped']);
    }
}
