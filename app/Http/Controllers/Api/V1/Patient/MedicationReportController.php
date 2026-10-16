<?php

namespace App\Http\Controllers\Api\V1\Patient;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class MedicationReportController extends Controller
{
    private function patient(Request $request): Patient
    {
        return Patient::where('user_id', $request->user()->id)
            ->firstOrFail();
    }

    private function getLogs(Request $request, Patient $patient)
    {
        $filters = $request->validate([
            'from' => ['nullable', 'date'],
            'to'   => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        return $patient->medicationLogs()
            ->with('medication')
            ->when(
                $filters['from'] ?? null,
                fn ($q, $date) => $q->whereDate('scheduled_at', '>=', $date)
            )
            ->when(
                $filters['to'] ?? null,
                fn ($q, $date) => $q->whereDate('scheduled_at', '<=', $date)
            )
            ->orderBy('scheduled_at')
            ->get();
    }

    public function index(Request $request)
    {
        $patient = $this->patient($request);
        $logs = $this->getLogs($request, $patient);

        return response()->json([
            'patient' => [
                'name'           => $patient->name,
                'patient_number' => $patient->patient_number,
            ],
            'total_doses' => $logs->count(),
            'taken'       => $logs->where('status', 'taken')->count(),
            'missed'      => $logs->where('status', 'missed')->count(),
            'skipped'     => $logs->where('status', 'skipped')->count(),
            'logs'        => $logs,
        ]);
    }

    public function pdf(Request $request)
    {
        $patient = $this->patient($request);
        $logs = $this->getLogs($request, $patient);

        $pdf = Pdf::loadView(
            'reports.medication-history',
            compact('patient', 'logs')
        );

        return $pdf->download('medication-history.pdf');
    }
}
