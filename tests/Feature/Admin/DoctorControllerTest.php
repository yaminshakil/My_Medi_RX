<?php

namespace Tests\Feature\Admin;

use App\Models\Doctor;
use App\Models\DoctorEducation;
use App\Models\DoctorExperience;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DoctorControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_doctor_show_renders_profile_and_professional_history(): void
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

        $user = User::factory()->create([
            'first_name' => 'Dr. Ayesha',
            'last_name'  => 'Rahman',
            'email'      => 'ayesha@example.com',
            'mobile'     => '01700000000',
        ]);
        $doctor = Doctor::query()->create([
            'user_id'           => $user->id,
            'phone'             => '01800000000',
            'registration_no'   => 'BMDC-TEST-1001',
            'gender'            => 'female',
            'dob'               => '1985-06-12',
            'specialization'    => 'Cardiology',
            'working_institute' => 'City Medical Center',
            'designation'       => 'Consultant',
            'qualification'     => 'MBBS, FCPS',
            'experience_years'  => 12,
            'bio'               => 'Cardiology specialist.',
            'active'            => true,
            'featured'          => true,
        ]);
        DoctorEducation::query()->create([
            'doctor_id' => $doctor->id,
            'degree'    => 'MBBS',
            'institute' => 'Dhaka Medical College',
            'year'      => '2008',
            'country'   => 'Bangladesh',
        ]);
        DoctorExperience::query()->create([
            'doctor_id'     => $doctor->id,
            'hospital_name' => 'City Medical Center',
            'position'      => 'Consultant Cardiologist',
            'from_year'     => '2018',
            'to_year'       => 'Present',
            'description'   => 'Outpatient and inpatient care.',
        ]);

        $this->actingAs($admin)
            ->get(route('doctors.show', $doctor->uuid))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Admin/Doctors/Show')
                ->where('doctor.user.name', 'Dr. Ayesha Rahman')
                ->where('doctor.registration_no', 'BMDC-TEST-1001')
                ->where('doctor.specialization', 'Cardiology')
                ->where('doctor.working_institute', 'City Medical Center')
                ->has('doctor.educations', 1)
                ->where('doctor.educations.0.degree', 'MBBS')
                ->has('doctor.experiences', 1)
                ->where('doctor.experiences.0.position', 'Consultant Cardiologist'));
    }
}
