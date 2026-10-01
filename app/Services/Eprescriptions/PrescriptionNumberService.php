<?php

namespace App\Services\Eprescriptions;

use App\Models\Prescription; // adjust if using another model
use Carbon\Carbon;

class PrescriptionNumberService
{
    public function generate()
    {
        $datePart = Carbon::now()->format('Ymd');

        // Count today's Prescriptions
        $countToday = Prescription::whereDate('created_at', Carbon::today())->count() + 1;

        // Format the serial number
        $serial = str_pad($countToday, 4, '0', STR_PAD_LEFT);

        // Final Prescription number
        return "PRS-{$datePart}-{$serial}";
    }
}
