<?php

namespace Tests\Feature;

use App\Interfaces\Eprescriptions\AppointmentRepositoryInterface;
use App\Models\Appointment;
use App\Models\DoctorChamber;
use App\Models\Patient;
use App\Models\User;
use App\Notifications\AppointmentBookedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AppointmentNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_doctor_receives_a_notification_when_they_create_an_appointment(): void
    {
        $doctor = User::factory()->create();
        $otherUser = User::factory()->create();
        $doctor->assignRole(Role::findOrCreate('Doctor', 'web'));
        $this->actingAs($doctor);

        $patient = Patient::create([
            'patient_number' => 'PAT-TEST-001',
            'name'           => 'Alex Smith',
            'phone'          => '01700000000',
        ]);
        $chamber = DoctorChamber::create([
            'user_id'      => $doctor->id,
            'doctor_id'    => $doctor->id,
            'name'         => 'Main Chamber',
            'city'         => 'Dhaka',
            'address'      => 'Dhaka',
            'header_left'  => '',
            'header_right' => '',
            'footer_info'  => '',
            'schedules'    => [],
            'fee'          => 0,
            'followup_fee' => 0,
            'is_active'    => true,
        ]);

        $appointment = app(AppointmentRepositoryInterface::class)->create([
            'doctor_id'        => $otherUser->id,
            'patient_id'       => $patient->id,
            'chamber_id'       => $chamber->id,
            'appointment_date' => '2026-10-28',
            'appointment_time' => '10:00:00',
        ]);

        $notification = $doctor->notifications()
            ->where('type', AppointmentBookedNotification::class)
            ->firstOrFail();

        $this->assertInstanceOf(Appointment::class, $appointment);
        $this->assertSame($doctor->id, $appointment->doctor_id);
        $this->assertSame('appointment_booked', $notification->data['type']);
        $this->assertSame((string) $appointment->getKey(), $notification->data['appointment_id']);
    }
}
