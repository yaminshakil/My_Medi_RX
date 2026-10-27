<?php

namespace Tests\Unit\Notifications;

use App\Models\Appointment;
use App\Models\Patient;
use App\Notifications\AppointmentBookedNotification;
use Tests\TestCase;

class AppointmentBookedNotificationTest extends TestCase
{
    public function test_it_formats_an_appointment_notification_for_the_doctor(): void
    {
        $appointment = new Appointment([
            'appointment_date' => '2026-10-28',
            'appointment_time' => '10:00:00',
        ]);
        $appointment->id = 42;
        $appointment->setRelation('patient', new Patient(['name' => 'Alex Smith']));

        $notification = new AppointmentBookedNotification($appointment);

        $this->assertSame(['database'], $notification->via(new \stdClass));
        $this->assertSame([
            'type'           => 'appointment_booked',
            'title'          => 'New Appointment',
            'message'        => 'Alex Smith booked an appointment for Oct 28, 2026 at 10:00 AM.',
            'appointment_id' => '42',
            'icon'           => 'calendarCheck',
            'colorHex'       => '#4CAF50',
            'actionRoute'    => '/admin/appointments',
        ], $notification->toDatabase(new \stdClass));
    }
}
