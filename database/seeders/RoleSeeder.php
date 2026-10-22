<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (['Admin', 'Doctor', 'Patient', 'Pharmacy', 'Hospital'] as $name) {
            Role::firstOrCreate([
                'name' => $name,
                'guard_name' => 'api',
            ]);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
