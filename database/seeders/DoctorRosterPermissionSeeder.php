<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class DoctorRosterPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'view doctor availability',
            'manage doctor shifts',
            'manage doctor availability',
            'manage doctor rosters',
            'request doctor leave',
            'approve doctor leaves',
        ];

        foreach ($permissions as $name) {
            Permission::firstOrCreate([
                'name'       => $name,
                'guard_name' => 'web',
            ]);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // Assign these permissions to your existing roles in your role seeder.
        // Avoid hard-coding a role name here because role names differ between installations.
    }
}
