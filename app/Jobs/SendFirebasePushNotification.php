<?php

namespace App\Jobs;

use App\Models\FirebaseDeviceToken;
use App\Services\FirebaseMessagingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendFirebasePushNotification implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    /**
     * @param  array<string, string>  $data
     */
    public function __construct(
        public int $deviceTokenId,
        public string $title,
        public string $message,
        public array $data,
    ) {
        $this->afterCommit();
    }

    public function handle(FirebaseMessagingService $firebaseMessaging): void
    {
        $deviceToken = FirebaseDeviceToken::find($this->deviceTokenId);

        if ($deviceToken === null) {
            return;
        }

        $firebaseMessaging->send($deviceToken->token, $this->title, $this->message, $this->data);
    }
}
