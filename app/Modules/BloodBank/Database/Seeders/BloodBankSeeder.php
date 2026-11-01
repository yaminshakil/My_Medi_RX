<?php

namespace App\Modules\BloodBank\Database\Seeders;

use App\Modules\BloodBank\Models\Patient;
use App\Modules\BloodBank\Models\Physician;
use App\Modules\BloodBank\Models\StorageUnit;
use Illuminate\Database\Seeder;

/**
 * Starter data so the screens work on a fresh install:
 *   php artisan db:seed --class="App\Modules\BloodBank\Database\Seeders\BloodBankSeeder"
 * Emergency broadcasts use the existing hospital linked through hospitals.created_by.
 */
class BloodBankSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            ['Fridge A', 'Main blood bank', 'refrigerator', 2.0, 6.0, 200],
            ['Fridge B', 'Main blood bank', 'refrigerator', 2.0, 6.0, 200],
            ['Platelet agitator 1', 'Main blood bank', 'platelet_agitator', 20.0, 24.0, 40],
            ['Plasma freezer', 'Cold room', 'freezer', -30.0, -18.0, 300],
        ] as [$name, $location, $type, $min, $max, $capacity]) {
            StorageUnit::firstOrCreate(['name' => $name], [
                'location'   => $location, 'type' => $type,
                'min_temp_c' => $min, 'max_temp_c' => $max, 'capacity' => $capacity,
            ]);
        }

        Physician::firstOrCreate(['license_no' => 'DEMO-001'], ['name' => 'Dr. Demo Physician', 'department' => 'Surgery']);

        Patient::firstOrCreate(['mrn' => 'MRN-0001'], [
            'name' => 'Demo Patient', 'clinical_file_no' => 'CF-0001', 'blood_group' => 'O+',
        ]);
    }
}
