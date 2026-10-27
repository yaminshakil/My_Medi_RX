<?php

namespace Tests\Unit\Notifications;

use App\Notifications\PrescriptionIssuedNotification;
use Tests\TestCase;

class PrescriptionIssuedNotificationTest extends TestCase
{
    public function test_it_formats_an_issued_prescription_for_the_patient(): void
    {
        $notification = new PrescriptionIssuedNotification(
            prescriptionId: '123',
            prescriptionNumber: 'RX-123',
            doctorName: 'Sarah Khan',
            actionRoute: '/api/v1/patient/prescriptions/123',
        );

        $this->assertSame(['database'], $notification->via(new \stdClass));
        $this->assertSame([
            'type'                => 'prescription_issued',
            'title'               => 'New Prescription',
            'message'             => 'Dr. Sarah Khan issued your prescription RX-123.',
            'prescription_id'     => '123',
            'prescription_number' => 'RX-123',
            'icon'                => 'checkCircle2',
            'colorHex'            => '#FF9800',
            'actionRoute'         => '/api/v1/patient/prescriptions/123',
        ], $notification->toDatabase(new \stdClass));
    }
}
