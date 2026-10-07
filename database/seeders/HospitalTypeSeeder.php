<?php

namespace Database\Seeders;

use App\Models\HospitalType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class HospitalTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            'Government Hospital',
            'Private Hospital',
            'Medical College Hospital',
            'Specialized Hospital',
            'Community Hospital',
            'Clinic',
            'Diagnostic Center',
        ];

        foreach ($types as $index => $name) {
            HospitalType::updateOrCreate(
                ['slug' => Str::slug($name)],
                [
                    'name' => $name,
                    'sort_order' => $index + 1,
                    'status' => true,
                ]
            );
        }
    }
}
