<?php

namespace App\Services\Eprescriptions;

use App\Models\Appointment; // adjust if using another model
use Carbon\Carbon;

class AppointmentNumberService
{
    public function generate()
    {
        $datePart = Carbon::now()->format('Ymd');

        // Count today's orders
        $countToday = Appointment::whereDate('created_at', Carbon::today())->count() + 1;

        // Format the serial number
        $serial = str_pad($countToday, 4, '0', STR_PAD_LEFT);

        // Final order number
        return "A-{$datePart}-{$serial}";
    }
}
