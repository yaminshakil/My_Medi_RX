<?php

namespace Database\Seeders;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Seeder;

class SubscriptionPlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            // PATIENT PLANS
            [
                'name'          => 'Patient Free',
                'slug'          => 'patient-free',
                'description'   => 'Basic healthcare management for patients.',
                'price'         => 0,
                'billing_cycle' => 'monthly',
                'duration_days' => 30,
                'user_type'     => 'Patient',
                'features'      => [
                    'ai_assistant'         => ['enabled' => true, 'limit' => 5],
                    'medicine_reminders'   => ['enabled' => true],
                    'prescription_history' => ['enabled' => true],
                    'prescription_qr'      => ['enabled' => false],
                    'advanced_reports'     => ['enabled' => false],
                ],
            ],
            [
                'name'          => 'Patient Premium',
                'slug'          => 'patient-premium',
                'description'   => 'Advanced healthcare features for patients.',
                'price'         => 299,
                'billing_cycle' => 'monthly',
                'duration_days' => 30,
                'user_type'     => 'Patient',
                'features'      => [
                    'ai_assistant'         => ['enabled' => true, 'limit' => -1],
                    'medicine_reminders'   => ['enabled' => true],
                    'prescription_history' => ['enabled' => true],
                    'prescription_qr'      => ['enabled' => true],
                    'advanced_reports'     => ['enabled' => true],
                ],
            ],

            // DOCTOR PLANS
            [
                'name'          => 'Doctor Basic',
                'slug'          => 'doctor-basic',
                'description'   => 'Essential tools for independent doctors.',
                'price'         => 499,
                'billing_cycle' => 'monthly',
                'duration_days' => 30,
                'user_type'     => 'Doctor',
                'features'      => [
                    'appointments'       => ['enabled' => true, 'limit' => 100],
                    'prescriptions'      => ['enabled' => true, 'limit' => 100],
                    'patient_management' => ['enabled' => true, 'limit' => 100],
                    'advanced_reports'   => ['enabled' => false],
                    'qr_prescriptions'   => ['enabled' => false],
                ],
            ],
            [
                'name'          => 'Doctor Professional',
                'slug'          => 'doctor-professional',
                'description'   => 'Advanced practice management for doctors.',
                'price'         => 999,
                'billing_cycle' => 'monthly',
                'duration_days' => 30,
                'user_type'     => 'Doctor',
                'features'      => [
                    'appointments'       => ['enabled' => true, 'limit' => -1],
                    'prescriptions'      => ['enabled' => true, 'limit' => -1],
                    'patient_management' => ['enabled' => true, 'limit' => 500],
                    'advanced_reports'   => ['enabled' => true],
                    'qr_prescriptions'   => ['enabled' => true],
                ],
            ],

            // PHARMACY PLANS
            [
                'name'          => 'Pharmacy Starter',
                'slug'          => 'pharmacy-starter',
                'description'   => 'Basic pharmacy inventory and prescription management.',
                'price'         => 999,
                'billing_cycle' => 'monthly',
                'duration_days' => 30,
                'user_type'     => 'Pharmacy',
                'features'      => [
                    'inventory_management' => ['enabled' => true],
                    'prescription_orders'  => ['enabled' => true, 'limit' => 100],
                    'stock_alerts'         => ['enabled' => true],
                    'advanced_reports'     => ['enabled' => false],
                    'multiple_branches'    => ['enabled' => false],
                ],
            ],
            [
                'name'          => 'Pharmacy Business',
                'slug'          => 'pharmacy-business',
                'description'   => 'Complete pharmacy business management.',
                'price'         => 1999,
                'billing_cycle' => 'monthly',
                'duration_days' => 30,
                'user_type'     => 'Pharmacy',
                'features'      => [
                    'inventory_management' => ['enabled' => true],
                    'prescription_orders'  => ['enabled' => true, 'limit' => -1],
                    'stock_alerts'         => ['enabled' => true],
                    'advanced_reports'     => ['enabled' => true],
                    'multiple_branches'    => ['enabled' => true],
                ],
            ],

            // HOSPITAL PLANS
            [
                'name'          => 'Hospital Standard',
                'slug'          => 'hospital-standard',
                'description'   => 'Hospital management for small and medium facilities.',
                'price'         => 2999,
                'billing_cycle' => 'monthly',
                'duration_days' => 30,
                'user_type'     => 'Hospital',
                'features'      => [
                    'doctor_management'      => ['enabled' => true, 'limit' => 20],
                    'appointment_management' => ['enabled' => true],
                    'department_management'  => ['enabled' => true],
                    'attendance_management'  => ['enabled' => false],
                    'advanced_reports'       => ['enabled' => false],
                ],
            ],
            [
                'name'          => 'Hospital Enterprise',
                'slug'          => 'hospital-enterprise',
                'description'   => 'Advanced hospital management for large healthcare organizations.',
                'price'         => 5999,
                'billing_cycle' => 'monthly',
                'duration_days' => 30,
                'user_type'     => 'Hospital',
                'features'      => [
                    'doctor_management'      => ['enabled' => true, 'limit' => -1],
                    'appointment_management' => ['enabled' => true],
                    'department_management'  => ['enabled' => true],
                    'attendance_management'  => ['enabled' => true],
                    'advanced_reports'       => ['enabled' => true],
                    'multiple_branches'      => ['enabled' => true],
                ],
            ],
        ];

        foreach ($plans as $plan) {
            SubscriptionPlan::updateOrCreate(
                ['slug' => $plan['slug']],
                array_merge($plan, ['is_active' => true])
            );
        }
    }
}
