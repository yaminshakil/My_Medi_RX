<?php

namespace Database\Seeders;

use App\Models\EmailTemplate;
use Illuminate\Database\Seeder;

class EmailTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $appointment_confirmation_shortcodes = [
            [
                'code' => '{{booking_date}}',
                'desc' => 'Booking date for appointment',
            ],
            [
                'code' => '{{time_serial}}',
                'desc' => 'Time or serial of appointment',
            ],
            [
                'code' => '{{booking_number}}',
                'desc' => 'Booking number for appointment',
            ],
            [
                'code' => '{{doctor_name}}',
                'desc' => 'Appointment doctor name',
            ],
            [
                'code' => '{{doctor_fees}}',
                'desc' => 'Doctor Fees',
            ],
            [
                'code' => '{{site_currency}}',
                'desc' => 'Currency of your site',
            ],
            [
                'code' => '{{currency_symbol}}',
                'desc' => 'Symbol of currency',
            ],
            [
                'code' => '{{booking_status}}',
                'desc' => 'Appointment Status',
            ],
        ];
        EmailTemplate::updateOrCreate(
            ['slug' => 'appointment_confirmation'],
            [
                'name'       => 'Appointment Confirmation',
                'subject'    => 'Your Appointment Confirmation',
                'body'       => 'We have received an appointment request for the date of {{booking_date}} and Serial/time/Slot: {{time_serial}} with Dr. {{doctor_name}}. Your request is confirmed. You have to pay {{site_currency}} {{doctor_fees}} {{currency_symbol}} after completing the appointment. Please be present there at due time. Thanks for being with us.',
                'shortcodes' => $appointment_confirmation_shortcodes,
            ]
        );

        EmailTemplate::updateOrCreate(
            ['slug' => 'appointment_status'],
            [
                'name'       => 'Appointment Update Status',
                'subject'    => 'Your Appointment Update Status',
                'body'       => 'We have {{booking_status}} your appointment request for the date of {{booking_date}} and time {{time_serial}} with Dr. {{doctor_name}}. We are suggested to try another schedule.Thanks for being with us.',
                'shortcodes' => $appointment_confirmation_shortcodes,
            ]
        );
    }
}
