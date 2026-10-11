<?php

namespace App\Modules\BloodBank\Notifications;

use App\Modules\BloodBank\Models\BloodDonor;
use App\Modules\BloodBank\Models\EmergencyRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

/** Sent to each nearby verified donor. Contains signed one-tap accept / decline links. */
class EmergencyBloodRequestNotification extends Notification implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public EmergencyRequest $request,
        public BloodDonor $donor,
        public float $distanceKm,
    ) {
    }

    public function via(object $notifiable): array
    {
        $channels = $this->donor->email ? ['mail'] : [];

        if ($extra = config('blood-bank.emergency.extra_channel')) {
            $channels[] = $extra;
        }

        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        $this->request->loadMissing('hospital');

        $mail = (new MailMessage)
            ->subject("{$this->request->urgency->label()}: {$this->request->blood_group->value} blood needed")
            ->greeting("Hello {$this->donor->name},")
            ->line(sprintf(
                '%s needs %d unit(s) of %s (%s). You are about %.1f km away.',
                $this->request->hospital->name,
                $this->request->units_needed,
                strtolower($this->request->component->label()),
                $this->request->blood_group->value,
                $this->distanceKm
            ));

        if ($this->request->note) {
            $mail->line($this->request->note);
        }

        return $mail
            ->action('I can donate', $this->link('accepted'))
            ->line('Cannot make it? [Decline this request]('.$this->link('declined').')');
    }

    /** Plain text for SMS gateways configured through blood-bank.emergency.extra_channel. */
    public function toSms(object $notifiable): string
    {
        $this->request->loadMissing('hospital');

        return sprintf(
            '%s needs %s %s. Can you donate? Yes: %s',
            $this->request->hospital->name,
            $this->request->blood_group->value,
            strtolower($this->request->component->label()),
            $this->link('accepted')
        );
    }

    // Signed + expiring link: the donor answers without logging in, and nobody can forge another donor's reply.
    private function link(string $response): string
    {
        return URL::temporarySignedRoute(
            'blood-bank.donor-response',
            now()->addHours(config('blood-bank.emergency.response_link_ttl_hours')),
            ['emergencyRequest' => $this->request->id, 'donor' => $this->donor->id, 'response' => $response]
        );
    }
}
