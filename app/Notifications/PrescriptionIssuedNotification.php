<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PrescriptionIssuedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $prescriptionId,
        public string $prescriptionNumber,
        public string $doctorName,
        public string $actionRoute,
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
        return [
            'type'                => 'prescription_issued',
            'title'               => 'New Prescription',
            'message'             => "Dr. {$this->doctorName} issued your prescription {$this->prescriptionNumber}.",
            'prescription_id'     => $this->prescriptionId,
            'prescription_number' => $this->prescriptionNumber,
            'icon'                => 'checkCircle2',
            'colorHex'            => '#FF9800',
            'actionRoute'         => $this->actionRoute,
        ];
    }
}
