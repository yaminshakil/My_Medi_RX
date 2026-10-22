<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PediatricDoseRulePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'pediatric-dose-rules.view',
            'pediatric-dose-rules.view-all',
            'pediatric-dose-rules.create',
            'pediatric-dose-rules.edit-own',
            'pediatric-dose-rules.edit-all',
            'pediatric-dose-rules.submit',
            'pediatric-dose-rules.approve',
            'pediatric-dose-rules.activate',
            'pediatric-dose-rules.audit-own',
            'pediatric-dose-rules.audit-all',
        ];

        $guards = ['web', 'api'];

        // 1. Create permissions for both guards
        foreach ($guards as $guard) {
            foreach ($permissions as $permission) {
                Permission::findOrCreate($permission, $guard);
            }
        }

        // 2. Assign permissions to roles for each guard
        foreach ($guards as $guard) {
            // Ensure roles exist for the guard before finding them (or use firstOrCreate)
            $doctor = Role::firstOrCreate(['name' => 'Doctor', 'guard_name' => $guard]);
            $admin = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => $guard]);

            $doctor->givePermissionTo([
                'pediatric-dose-rules.view',
                'pediatric-dose-rules.create',
                'pediatric-dose-rules.edit-own',
                'pediatric-dose-rules.submit',
                'pediatric-dose-rules.audit-own',
            ]);

            $admin->givePermissionTo([
                'pediatric-dose-rules.view',
                'pediatric-dose-rules.view-all',
                'pediatric-dose-rules.create',
                'pediatric-dose-rules.edit-own',
                'pediatric-dose-rules.edit-all',
                'pediatric-dose-rules.submit',
                'pediatric-dose-rules.approve',
                'pediatric-dose-rules.activate',
                'pediatric-dose-rules.audit-own',
                'pediatric-dose-rules.audit-all',
            ]);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
