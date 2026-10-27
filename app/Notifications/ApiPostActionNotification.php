<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ApiPostActionNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $action,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, string|null>
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'type'        => 'api_post_action',
            'title'       => 'Action completed',
            'message'     => "The {$this->action} action completed successfully.",
            'actionRoute' => null,
        ];
    }
}
