<?php

namespace App\Notifications;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AppointmentBookedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Appointment $appointment,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, string>
     */
    public function toDatabase(object $notifiable): array
    {
        $patientName = $this->appointment->patient?->name ?? 'A patient';
        $appointmentDate = $this->appointment->appointment_date->format('M j, Y');
        $appointmentTime = $this->appointment->appointment_time->format('g:i A');

        return [
            'type'           => 'appointment_booked',
            'title'          => 'New Appointment',
            'message'        => "{$patientName} booked an appointment for {$appointmentDate} at {$appointmentTime}.",
            'appointment_id' => (string) $this->appointment->getKey(),
            'icon'           => 'calendarCheck',
            'colorHex'       => '#4CAF50',
            'actionRoute'    => '/admin/appointments',
        ];
    }
}
