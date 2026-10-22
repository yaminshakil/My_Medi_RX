<?php

namespace App\Services\Pediatric;

use App\Models\PediatricDoseCalculation;
use App\Models\PediatricDoseRule;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PediatricDoseCalculationService
{
    public function calculate(array $data, int $userId): array
    {
        return DB::transaction(function () use ($data, $userId) {
            $rules = PediatricDoseRule::query()
                ->approved()
                ->where('medicine_id', $data['medicineId'])
                ->whereRaw('LOWER(formulation) = ?', [
                    mb_strtolower(trim($data['formulation'])),
                ])
                ->whereRaw('LOWER(indication) = ?', [
                    mb_strtolower(trim($data['indication'])),
                ])
                ->where('min_age_months', '<=', $data['childAgeMonths'])
                ->where(function (Builder $query) use ($data) {
                    $query->whereNull('max_age_months')
                        ->orWhere(
                            'max_age_months',
                            '>=',
                            $data['childAgeMonths']
                        );
                })
                ->where(function (Builder $query) use ($data) {
                    $query->whereNull('min_weight_kg')
                        ->orWhere(
                            'min_weight_kg',
                            '<=',
                            $data['childWeightKg']
                        );
                })
                ->where(function (Builder $query) use ($data) {
                    $query->whereNull('max_weight_kg')
                        ->orWhere(
                            'max_weight_kg',
                            '>=',
                            $data['childWeightKg']
                        );
                })
                ->lockForUpdate()
                ->get();

            if ($rules->isEmpty()) {
                throw ValidationException::withMessages([
                    'dose_rule' => [
                        'No approved dose rule matches this medicine, '
                        . 'formulation, indication, age and weight.',
                    ],
                ]);
            }

            if ($rules->count() > 1) {
                throw ValidationException::withMessages([
                    'dose_rule' => [
                        'Multiple approved rules match these inputs. '
                        . 'A clinician must resolve the ambiguity.',
                    ],
                ]);
            }

            $rule = $rules->first();

            $weight = (float) $data['childWeightKg'];
            $concentrationMg = (float) $rule->concentration_mg;
            $concentrationMl = (float) $rule->concentration_ml;
            $mgPerKg = (float) $rule->dose_mg_per_kg_per_dose;

            if (
                $weight <= 0 ||
                $concentrationMg <= 0 ||
                $concentrationMl <= 0 ||
                $mgPerKg <= 0
            ) {
                throw ValidationException::withMessages([
                    'dose_rule' => [
                        'The approved rule contains invalid calculation values.',
                    ],
                ]);
            }

            // Calculate from the clinician-approved rule.
            $doseMg = $weight * $mgPerKg;

            if (
                $rule->max_single_dose_mg !== null &&
                $doseMg > (float) $rule->max_single_dose_mg
            ) {
                throw ValidationException::withMessages([
                    'dose' => [
                        'The calculated dose exceeds the approved single-dose limit. '
                        . 'No dose result was issued.',
                    ],
                ]);
            }

            $maxDoses = $rule->max_doses_per_24_hours;

            if (
                $maxDoses !== null &&
                $rule->max_daily_mg_per_kg !== null &&
                ($doseMg * $maxDoses) >
                    ($weight * (float) $rule->max_daily_mg_per_kg)
            ) {
                throw ValidationException::withMessages([
                    'dose' => [
                        'The configured dose and frequency exceed the '
                        . 'approved daily mg/kg limit.',
                    ],
                ]);
            }

            if (
                $maxDoses !== null &&
                $rule->max_daily_dose_mg !== null &&
                ($doseMg * $maxDoses) >
                    (float) $rule->max_daily_dose_mg
            ) {
                throw ValidationException::withMessages([
                    'dose' => [
                        'The configured dose and frequency exceed the '
                        . 'approved maximum daily dose.',
                    ],
                ]);
            }

            // Concentration is expressed as concentrationMg per concentrationMl.
            $volumeMl = $doseMg * $concentrationMl / $concentrationMg;

            // Check the rounded result still respects the configured limits.
            $roundedDoseMg = round($doseMg, 3);
            $roundedVolumeMl = round($volumeMl, 3);

            if (
                $rule->max_single_dose_mg !== null &&
                $roundedDoseMg > (float) $rule->max_single_dose_mg
            ) {
                throw ValidationException::withMessages([
                    'dose' => [
                        'The rounded dose exceeds the approved single-dose limit.',
                    ],
                ]);
            }

            $calculation = PediatricDoseCalculation::create([
                'user_id' => $userId,
                'medicine_id' => $rule->medicine_id,
                'pediatric_dose_rule_id' => $rule->id,
                'child_weight_kg' => $weight,
                'child_age_months' => $data['childAgeMonths'],
                'indication' => $rule->indication,
                'formulation' => $rule->formulation,
                'calculated_dose_mg' => $roundedDoseMg,
                'calculated_volume_ml' => $roundedVolumeMl,
                'concentration_mg' => $rule->concentration_mg,
                'concentration_ml' => $rule->concentration_ml,
                'rule_dose_mg_per_kg' => $rule->dose_mg_per_kg_per_dose,
                'max_single_dose_mg' => $rule->max_single_dose_mg,
                'route' => $rule->route,
                'dose_interval_hours' => $rule->dose_interval_hours,
                'max_doses_per_24_hours' => $rule->max_doses_per_24_hours,
                'source_reference' => $rule->source_reference,
                'status' => 'calculated',
                'safety_checks' => [
                    'approved_rule_matched' => true,
                    'age_range_matched' => true,
                    'weight_range_matched' => true,
                    'single_dose_limit_checked' => true,
                    'daily_limit_checked' => true,
                ],
                'calculated_at' => now(),
            ]);

            return [
                'calculationId' => $calculation->uuid,
                'medicineId' => $calculation->medicine_id,
                'formulation' => $calculation->formulation,
                'indication' => $calculation->indication,
                'childWeightKg' => (float) $calculation->child_weight_kg,
                'childAgeMonths' => $calculation->child_age_months,
                'doseMg' => (float) $calculation->calculated_dose_mg,
                'volumeMl' => (float) $calculation->calculated_volume_ml,
                'route' => $calculation->route,
                'doseIntervalHours' => $calculation->dose_interval_hours,
                'maxDosesPer24Hours' => $calculation->max_doses_per_24_hours,
                'sourceReference' => $calculation->source_reference,
                'status' => $calculation->status,
                'calculatedAt' => $calculation->calculated_at?->toISOString(),
                'safetyChecks' => $calculation->safety_checks,
            ];
        });
    }

    public function getRecentCalculations(
        int $userId,
        int $limit = 10
    ): array {
        return PediatricDoseCalculation::query()
            ->with('medicine')
            ->where('user_id', $userId)
            ->latest('calculated_at')
            ->limit(min(max($limit, 1), 50))
            ->get()
            ->map(fn (PediatricDoseCalculation $item) => [
                'calculationId' => $item->uuid,
                'medicineId' => $item->medicine_id,
                'medicineName' => $item->medicine?->name,
                'formulation' => $item->formulation,
                'indication' => $item->indication,
                'childWeightKg' => (float) $item->child_weight_kg,
                'childAgeMonths' => $item->child_age_months,
                'doseMg' => $item->calculated_dose_mg !== null
                    ? (float) $item->calculated_dose_mg
                    : null,
                'volumeMl' => $item->calculated_volume_ml !== null
                    ? (float) $item->calculated_volume_ml
                    : null,
                'status' => $item->status,
                'calculatedAt' => $item->calculated_at?->toISOString(),
            ])
            ->all();
    }

    public function getAvailableMedicines(): array
    {
        return PediatricDoseRule::query()
            ->approved()
            ->with('medicine')
            ->orderBy('medicine_id')
            ->get()
            ->unique(fn (PediatricDoseRule $rule) => implode('|', [
                $rule->medicine_id,
                mb_strtolower($rule->formulation),
                mb_strtolower($rule->indication),
            ]))
            ->values()
            ->map(fn (PediatricDoseRule $rule) => [
                'medicineId' => $rule->medicine_id,
                'medicineName' => $rule->medicine?->name,
                'formulation' => $rule->formulation,
                'indication' => $rule->indication,
                'route' => $rule->route,
            ])
            ->all();
    }
}
