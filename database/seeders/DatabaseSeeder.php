<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            PermissionSeeder::class,
            AdminMenuSeeder::class,
            CountriesTableSeeder::class,
            SettingSeeder::class,
            MedicalSpecialtiesSeeder::class,
            HospitalTypeSeeder::class,
            StaticTranslationsSeeder::class,
            SuperAdminSeeder::class,
        ]);
    }
}
