<?php

namespace App\Http\Controllers\Api\V1\Patient;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PatientMedication;
use App\Services\Patients\MedicationHeatmapService;
use Illuminate\Http\JsonResponse;

class PatientDashboardController extends Controller
{
    public function __construct(
        protected MedicationHeatmapService $heatmapService
    ) {
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // Use the existing User -> Patient relationship.
        $patient = $user->patient;
        $taken = 2;

        $prescriptions = $patient?->prescriptions()->with('medications.medicine')->get();

        $medications = $prescriptions?->flatMap(
            fn ($prescription) => $prescription->medications
        );

        if (! $patient) {
            return response()->json([
                'success'    => false,
                'statusCode' => 404,
                'message'    => 'Patient profile not found',
                'data'       => null,
            ], 404);
        }

        $now = now();

        $greeting = match (true) {
            $now->hour < 12 => 'Good morning',
            $now->hour < 17 => 'Good afternoon',
            default         => 'Good evening',
        };

        // Use the existing Patient -> Prescription relationship.
        $prescriptions = $patient?->prescriptions();

        $activePrescriptions = (clone $prescriptions)
            ->where('status', 'issued')
            ->count();

        // Use the existing Patient -> Appointment relationship.
        $upcomingAppointments = $patient->appointments()
            ->whereDate('appointment_date', '>=', $now->toDateString())
            ->where('status', '!=', 'completed')
            ->count();
        /*
         * Medication summary.
         *
         * Each PrescriptionMedicine record is counted once.
         * Actual taken doses require a medication intake log.
         */
        $total = $medications->count();

        $patientMedications = PatientMedication::where('patient_id', $patient->id)
    ->where('status', 'active')
    ->whereDate('start_date', '<=', $now->toDateString())
    ->where(function ($query) use ($now) {
        $query->whereNull('end_date')
            ->orWhereDate('end_date', '>=', $now->toDateString());
    })
    ->with('logs')
    ->get();

        $total = $patientMedications->count();

        $taken = $patientMedications->filter(
            fn ($medication) => $medication->logs->contains(
                fn ($log) => $log->created_at?->isSameDay($now)
            )
        )->count();

        $pending = max(0, $total - $taken);

        $adherenceRate = $total > 0
            ? round(($taken / $total) * 100, 1)
            : 0.0;

        return response()->json([
            'success'    => true,
            'statusCode' => 200,
            'message'    => 'Dashboard data retrieved',

            'data' => [
                'patient' => [
                    'id'       => (string) $patient->id,
                    'name'     => $user->name ?? $patient->name,
                    'eamil'    => $user->email ?? $patient->email,
                    'healthId' => $patient->patient_number,
                    'greeting' => $greeting.', '
                        .($user->name ?? $patient->name),
                ],

                // Dose-level adherence tracking is not present
                // in the models provided so far.
                'adherenceRate' => $adherenceRate,

                'todayMedsSummary' => [
                    'total'   => $total,
                    'taken'   => $taken,
                    'pending' => $pending,
                ],

                // Populate after confirming medication schedule
                // and PrescriptionMedicine fields.
                'todayMedications' => $medications->map(function ($item) {
                    return [
                        'id'     => (string) $item->id,
                        'name'   => $item->medicine?->brand_name ?? 'Medication',
                        'dosage' => trim(
                            ($item->dosage ?? '').' '.($item->strength ?? '')
                        ),
                        'time'         => $item->meal_time,
                        'isTaken'      => null,
                        'instructions' => $item->advice,
                    ];
                })->values(),

                'quickStats' => [
                    'activePrescriptions'  => $activePrescriptions,
                    'upcomingAppointments' => $upcomingAppointments,

                    // Connect your vaccination module here.
                    'pendingVaccinations' => 0,
                ],
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function heatmap(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'month' => ['required', 'integer', 'between:1,12'],
            'year' => ['required', 'integer', 'between:2000,2100'],
        ]);

        $patient = $request->user()?->patient;

        if (!$patient) {
            return response()->json([
                'success' => false,
                'statusCode' => 404,
                'message' => 'Patient profile not found',
                'data' => null,
            ], 404);
        }

        $data = $this->heatmapService->getMonthlyHeatmap(
            $patient,
            (int) $validated['month'],
            (int) $validated['year']
        );

        return response()->json([
            'success' => true,
            'statusCode' => 200,
            'message' => 'Monthly heatmap retrieved',
            'data' => $data,
        ]);
    }
}
