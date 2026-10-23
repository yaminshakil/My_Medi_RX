<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PediatricDoseRule;

class PediatricDoseRuleSeeder extends Seeder
{
    public function run(): void
    {
        // Ambroxol 15 mg/5 ml Syrup Rules
        $rules = [
            [
                'medicine_id' => 4,
                'formulation' => 'Syrup 15 mg/5 ml',
                'indication' => 'Cough',
                'route' => 'oral',
                'min_age_months' => 1,
                'max_age_months' => 23,
                'min_weight_kg' => 3.0,
                'max_weight_kg' => 12.0,
                'concentration_mg' => 15.0,
                'concentration_ml' => 5.0,
                'dose_mg_per_kg_per_dose' => 1.2, // or fixed mg mapping rule
                'max_single_dose_mg' => 7.5,    // 2.5 ml = 7.5 mg
                'max_daily_mg_per_kg' => 2.4,
                'max_daily_dose_mg' => 15.0,
                'dose_interval_hours' => 12,
                'max_doses_per_24_hours' => 2,
                'source_reference' => 'WHO / Standard Pediatric Formulary',
                'status' => 'approved',
                'approved_at' => now(),
                'is_active' => true,
                'approved_by' => 1,
            ],
            [
                'medicine_id' => 4,
                'formulation' => 'Syrup 15 mg/5 ml',
                'indication' => 'Cough',
                'route' => 'oral',
                'min_age_months' => 24,
                'max_age_months' => 72,
                'min_weight_kg' => 10.0,
                'max_weight_kg' => 25.0,
                'concentration_mg' => 15.0,
                'concentration_ml' => 5.0,
                'dose_mg_per_kg_per_dose' => 1.5,
                'max_single_dose_mg' => 15.0,   // 5 ml = 15 mg
                'max_daily_mg_per_kg' => 4.5,
                'max_daily_dose_mg' => 45.0,
                'dose_interval_hours' => 8,
                'max_doses_per_24_hours' => 3,
                'source_reference' => 'WHO / Standard Pediatric Formulary',
                'status' => 'approved',
                'approved_at' => now(),
                'is_active' => true,
                'approved_by' => 1,
            ],
            // Paracetamol 120 mg/5 mL Syrup Rule (Fever)
            [
                'medicine_id' => 3,
                'formulation' => 'Syrup 120 mg/5 mL',
                'indication' => 'Fever',
                'route' => 'oral',
                'min_age_months' => 8,
                'max_age_months' => 120,
                'min_weight_kg' => 8.0,
                'max_weight_kg' => 35.0,
                'concentration_mg' => 120.0,
                'concentration_ml' => 5.0,
                'dose_mg_per_kg_per_dose' => 12.5,
                'max_single_dose_mg' => 500.0,
                'max_daily_mg_per_kg' => 60.0,
                'max_daily_dose_mg' => 2000.0,
                'dose_interval_hours' => 6,
                'max_doses_per_24_hours' => 4,
                //'warnings' => 'Medicine-specific, source-verified warnings',
                'source_reference' => 'Full title and section of a verified clinical reference',
                'status' => 'approved',
                'approved_at' => now(),
                'approved_by' => 1,
                'is_active' => true,
            ],
        ];

        foreach ($rules as $rule) {
            PediatricDoseRule::updateOrCreate(
                [
                    'medicine_id' => $rule['medicine_id'],
                    'formulation' => $rule['formulation'],
                    'min_age_months' => $rule['min_age_months'],
                    'max_age_months' => $rule['max_age_months'],
                ],
                $rule
            );
        }
    }
}
