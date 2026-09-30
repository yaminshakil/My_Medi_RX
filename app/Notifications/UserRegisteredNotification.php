<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class UserRegisteredNotification extends Notification
{
    use Queueable;

    public function __construct(
        public User $user
    ) {
    }

    /**
     * Notification channels.
     */
    public function via(object $notifiable): array
    {
        return [
            'database',
            'broadcast',
        ];
    }

    /**
     * Database notification.
     */
    public function toDatabase(object $notifiable): array
    {
        return $this->notificationData();
    }

    /**
     * Broadcast notification.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage(
            $this->notificationData()
        );
    }

    /**
     * Notification data.
     */
    protected function notificationData(): array
    {
        $role = $this->user->getRoleNames()->first() ?? 'User';

        return [
            'type' => 'user_registered',

            'title' => 'New Registration',

            'message' => $this->user->first_name
                . ' '
                . $this->user->last_name
                . ' registered as a '
                . $role
                . '.',

            'user_id' => $this->user->id,

            'registration_type' => $role,

            'url' => route(
                'users.show',
                $this->user->id
            ),
        ];
    }
}
