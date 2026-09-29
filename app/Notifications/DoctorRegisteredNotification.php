<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Doctor;

class DoctorRegisteredNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public Doctor $doctor)
    {
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }


    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'doctor_registered',
            'title' => 'New Doctor Registration',
            'message' => 'Dr. ' . $this->doctor->user->name . ' has registered.',
            'doctor_id' => $this->doctor->id,
            'user_id' => $this->doctor->user_id,
            'url' => route('admin.doctors.show', $this->doctor->id),
        ];
    }
}
