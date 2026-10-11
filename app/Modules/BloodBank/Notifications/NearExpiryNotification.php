<?php

namespace App\Modules\BloodBank\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/** Daily digest for blood bank staff: what is about to expire and what just expired. */
class NearExpiryNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /** @param string[] $lines e.g. "A+ Platelets: 3 unit(s), earliest 12 Oct 06:00" */
    public function __construct(public array $lines, public int $expiredCount)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)->subject('Blood units nearing expiry');

        if ($this->expiredCount > 0) {
            $mail->line("{$this->expiredCount} unit(s) passed their expiry date and were removed from available stock.");
        }

        foreach ($this->lines as $line) {
            $mail->line($line);
        }

        return $mail->action('Open blood units', route('blood-bank.bags.index', ['expiry' => 'expiring']));
    }

    public function toArray(object $notifiable): array
    {
        return ['expired' => $this->expiredCount, 'expiring' => $this->lines];
    }
}
