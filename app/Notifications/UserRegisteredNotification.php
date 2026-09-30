<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Broadcasting\PrivateChannel;

class UserRegisteredNotification extends Notification implements ShouldBroadcastNow
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
        \Log::info('🔥 Broadcasting user registration notification', [
            'admin_id' => $notifiable->id,
            'registered_user_id' => $this->user->id,
        ]);

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
            'url' => $this->user->id
            ? route('users.show', ['user' => $this->user->id])
            : '#',
        ];
    }
}
