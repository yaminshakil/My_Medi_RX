<?php

namespace Tests\Feature\Dashboard;

use App\Models\Appointment;
use App\Models\DoctorChamber;
use App\Models\Hospital;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DashboardControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_hospital_dashboard_shows_only_its_chambers_and_summary_counts(): void
    {
        $this->travelTo('2026-11-03 10:00:00');
        $owner = $this->userWithRole('Hospital');
        $otherOwner = $this->userWithRole('Hospital');
        $doctor = User::factory()->create([
            'first_name' => 'Dr. Alex',
            'last_name'  => 'Carter',
        ]);
        $hospital = $this->createHospital($owner, 'Central Care Hospital');
        $otherHospital = $this->createHospital($otherOwner, 'Other Hospital');
        $activeChamber = $this->createChamber($hospital, $doctor, 'Cardiology', true);
        $this->createChamber($hospital, $doctor, 'Neurology', false);
        $otherChamber = $this->createChamber($otherHospital, $doctor, 'Other Chamber', true);
        $patient = Patient::query()->create([
            'patient_number' => 'PT-1001',
            'name'           => 'Dashboard Patient',
            'phone'          => '01700000000',
        ]);
        $this->createAppointment('APT-1001', $doctor, $patient, $activeChamber, today()->toDateString(), 'pending');
        $this->createAppointment('APT-1002', $doctor, $patient, $activeChamber, today()->toDateString(), 'confirmed');
        $this->createAppointment('APT-1003', $doctor, $patient, $activeChamber, today()->addDay()->toDateString(), 'pending');
        $this->createAppointment('APT-1004', $doctor, $patient, $otherChamber, today()->toDateString(), 'pending');

        $this->actingAs($owner)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Dashboard/HospitalDashboard')
                ->where('hospital.name', 'Central Care Hospital')
                ->where('stats.totalDoctors', 1)
                ->where('stats.totalChambers', 2)
                ->where('stats.activeChambers', 1)
                ->where('stats.todayAppointments', 2)
                ->where('stats.pendingAppointments', 2)
                ->has('chambers', 2)
                ->where('chambers.0.name', 'Cardiology')
                ->where('chambers.0.doctor.name', 'Dr. Alex Carter'));
    }

    public function test_hospital_dashboard_displays_empty_state_when_no_hospital_is_linked(): void
    {
        $owner = $this->userWithRole('Hospital');

        $this->actingAs($owner)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Dashboard/HospitalDashboard')
                ->where('hospital', null)
                ->where('stats.totalDoctors', 0)
                ->where('stats.totalChambers', 0)
                ->where('stats.activeChambers', 0)
                ->where('stats.todayAppointments', 0)
                ->where('stats.pendingAppointments', 0)
                ->has('chambers', 0));
    }

    private function userWithRole(string $roleName): User
    {
        $role = Role::firstOrCreate([
            'name'       => $roleName,
            'guard_name' => 'web',
        ]);
        $user = User::factory()->create([
            'first_name' => 'Hospital',
            'last_name'  => 'Owner',
        ]);
        $user->assignRole($role);

        return $user;
    }

    private function createHospital(User $owner, string $name): Hospital
    {
        return Hospital::query()->create([
            'created_by'          => $owner->id,
            'hospital_name'       => $name,
            'mobile_number'       => '01700000000',
            'address'             => '12 Main Road, Dhaka',
            'verification_status' => 'approved',
        ]);
    }

    private function createChamber(
        Hospital $hospital,
        User $doctor,
        string $name,
        bool $isActive
    ): DoctorChamber {
        return DoctorChamber::query()->create([
            'hospital_id'  => $hospital->id,
            'user_id'      => $doctor->id,
            'doctor_id'    => $doctor->id,
            'name'         => $name,
            'city'         => 'Dhaka',
            'address'      => '12 Main Road',
            'header_left'  => '',
            'header_right' => '',
            'footer_info'  => '',
            'schedules'    => [],
            'fee'          => 800,
            'followup_fee' => 500,
            'is_active'    => $isActive,
        ]);
    }

    private function createAppointment(
        string $number,
        User $doctor,
        Patient $patient,
        DoctorChamber $chamber,
        string $date,
        string $status
    ): Appointment {
        return Appointment::query()->create([
            'appointment_number' => $number,
            'doctor_id'          => $doctor->id,
            'patient_id'         => $patient->id,
            'chamber_id'         => $chamber->id,
            'appointment_date'   => $date,
            'appointment_time'   => '09:00:00',
            'status'             => $status,
        ]);
    }
}
