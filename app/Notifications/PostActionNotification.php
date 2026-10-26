<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class PostActionNotification extends Notification implements ShouldBroadcastNow
{
    use Queueable;

    public function __construct(
        public string $action,
        public string $url,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toDatabase(object $notifiable): array
    {
        return $this->notificationData();
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->notificationData());
    }

    /**
     * @return array<string, string>
     */
    private function notificationData(): array
    {
        return [
            'type'    => 'post_action',
            'title'   => 'POST action completed',
            'message' => "The {$this->action} action completed successfully.",
            'url'     => $this->url,
        ];
    }
}
