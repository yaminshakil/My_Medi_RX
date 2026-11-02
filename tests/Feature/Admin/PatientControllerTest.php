<?php

namespace Tests\Feature\Admin;

use App\Models\Medicine;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\PrescriptionMedicine;
use App\Models\User;
use App\Models\Vital;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PatientControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_patient_show_renders_medirx_profile_vitals_and_prescriptions(): void
    {
        $adminRole = Role::firstOrCreate([
            'name'       => 'Admin',
            'guard_name' => 'web',
        ]);
        $admin = User::factory()->create([
            'first_name' => 'Admin',
            'last_name'  => 'User',
        ]);
        $admin->assignRole($adminRole);

        $doctor = User::factory()->create([
            'first_name' => 'Doctor',
            'last_name'  => 'User',
        ]);
        $patient = Patient::query()->create([
            'patient_number' => 'MRX-0001',
            'name'           => 'Samira Akter',
            'date_of_birth'  => '1990-04-15',
            'gender'         => 'female',
            'phone'          => '01700000000',
            'email'          => 'samira@example.com',
            'blood_group'    => 'O+',
            'address'        => '12 Lake Road',
            'city'           => 'Dhaka',
        ]);
        $vital = Vital::query()->create([
            'patient_id'     => $patient->id,
            'blood_pressure' => '120/80',
            'heart_rate'     => 72,
        ]);
        $prescription = Prescription::query()->create([
            'patient_id'          => $patient->id,
            'doctor_id'           => $doctor->id,
            'prescription_number' => 'RX-0001',
            'diagnosis'           => 'Seasonal allergy',
            'followup_date'       => '2026-11-15',
            'header_left'         => '',
            'header_right'        => '',
            'footer_info'         => '',
        ]);
        $medicine = Medicine::query()->create([
            'doctor_id'    => $doctor->id,
            'generic_name' => 'Cetirizine',
            'brand_name'   => 'AllerClear',
            'strength'     => '10 mg',
            'type'         => 'Tablet',
        ]);
        PrescriptionMedicine::query()->create([
            'prescription_id' => $prescription->id,
            'medicine_id'     => $medicine->id,
            'type'            => 'Tablet',
            'strength'        => '10 mg',
            'dosage'          => 'Once daily',
            'duration'        => '5 days',
        ]);

        $this->actingAs($admin)
            ->get(route('patients.show', $patient->id))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Admin/Patients/Show')
                ->where('patient.name', 'Samira Akter')
                ->where('patient.patient_number', 'MRX-0001')
                ->where('patient.email', 'samira@example.com')
                ->has('patient.vitals', 1)
                ->where('patient.vitals.0.id', $vital->id)
                ->where('patient.vitals.0.blood_pressure', '120/80')
                ->has('patient.prescriptions', 1)
                ->where('patient.prescriptions.0.prescription_number', 'RX-0001')
                ->where('patient.prescriptions.0.diagnosis', 'Seasonal allergy')
                ->where('patient.prescriptions.0.medications.0.medicine.generic_name', 'Cetirizine'));
    }
}
