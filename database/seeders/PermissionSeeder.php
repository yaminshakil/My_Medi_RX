<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear Spatie permission cache.
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'View',
            'Create',
            'Edit',
            'Delete',
            'Appointment Create',
            'Appointment Edit',
            'Appointment Delete',
            'users.menu',
            'roles.menu',
            'permissions.menu',
            'patients.menu',
            'appointments.menu',
        ];

        /*
         * Create permissions if they don't already exist.
         */
        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name'       => $permission,
                'guard_name' => 'web',
            ]);
        }

        /*
         * Admin
         */
        $adminRole = Role::firstOrCreate([
            'name'       => 'Admin',
            'guard_name' => 'web',
        ]);

        $adminRole->syncPermissions([
            'View',
            'Create',
            'Edit',
            'Delete',
            'users.menu',
            'roles.menu',
            'permissions.menu',
            'Appointment Create',
            'Appointment Edit',
            'Appointment Delete',
        ]);

        /*
         * Doctor
         */
        $doctorRole = Role::firstOrCreate([
            'name'       => 'Doctor',
            'guard_name' => 'web',
        ]);

        $doctorRole->syncPermissions([
            'View',
            'Create',
            'Edit',
            'Delete',
            'patients.menu',
            'appointments.menu',
            'Appointment Create',
            'Appointment Edit',
            'Appointment Delete',
        ]);

        /*
         * Patient
         */
        $patientRole = Role::firstOrCreate([
            'name'       => 'Patient',
            'guard_name' => 'web',
        ]);

        $patientRole->syncPermissions([
            'View',
            'Delete',
            'appointments.menu',
            'Appointment Create',
            'Appointment Edit',
            'Appointment Delete',
        ]);

        /*
         * Pharmacy
         */
        $patientRole = Role::firstOrCreate([
            'name'       => 'Pharmacy',
            'guard_name' => 'web',
        ]);

        $patientRole->syncPermissions([
            'View',
            'Create',
            'Edit',
            'Delete',
        ]);

        /*
         * Hospital
         */
        $patientRole = Role::firstOrCreate([
            'name'       => 'Hospital',
            'guard_name' => 'web',
        ]);

        $patientRole->syncPermissions([
            'View',
            'Create',
            'Edit',
            'Delete',
        ]);

        /*
         * Clear permission cache again after assigning permissions.
         */
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
