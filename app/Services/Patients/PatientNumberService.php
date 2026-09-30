<?php

namespace App\Services\Patients;

use App\Models\Patient; // adjust if using another model
use Carbon\Carbon;

class PatientNumberService
{
    public function generate()
    {
        $datePart = Carbon::now()->format('Ymd');

        // Count today's ptients
        $countToday = Patient::whereDate('created_at', Carbon::today())->count() + 1;

        // Format the serial number
        $serial = str_pad($countToday, 4, '0', STR_PAD_LEFT);

        // Final Patient number
        return "P-{$datePart}-{$serial}";
    }
}
