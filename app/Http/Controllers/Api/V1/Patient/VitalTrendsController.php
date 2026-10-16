<?php

namespace App\Http\Controllers\Api\V1\Patient;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Vital;
use Carbon\Carbon;

class VitalTrendsController extends Controller
{
    public function getVitalsTrends(Request $request, int $id)
    {
        // 1. Get 'days' from query parameter, defaulting to 7 if not provided
        $daysCount = (int) $request->input('days', 7);
        $patientId = $id;

        // 2. Generate dynamic dates list based on the requested days count
        $dates = [];
        for ($i = $daysCount - 1; $i >= 0; $i--) {
            $dates[] = Carbon::now()->subDays($i)->format('D'); // e.g., Mon, Tue or format('Y-m-d')
        }

        // 3. Fetch vitals from database for the specified timeframe
        $vitals = Vital::where('patient_id', $patientId)
            ->where('created_at', '>=', Carbon::now()->subDays($daysCount))
            ->orderBy('created_at', 'asc')
            ->get();

        // 4. Map values for each date slot
        $heartRates = [];
        $systolicValues = [];
        $diastolicValues = [];
        $spo2Values = [];
        $glucoseValues = [];
        $temperatureValues = [];

        // Loop through each dynamic date slot to align values
        for ($i = $daysCount - 1; $i >= 0; $i--) {
            $targetDate = Carbon::now()->subDays($i)->format('Y-m-d');

            // Find record matching this specific date
            $record = $vitals->first(fn ($v) => $v->created_at->format('Y-m-d') === $targetDate);

            // Parse blood pressure if stored as "120/80"
            $bpParts = $record && $record->blood_pressure ? explode('/', $record->blood_pressure) : [120, 80];

            $heartRates[] = $record ? (int) $record->heart_rate : null;
            $systolicValues[] = isset($bpParts[0]) ? (int) $bpParts[0] : null;
            $diastolicValues[] = isset($bpParts[1]) ? (int) $bpParts[1] : null;
            $spo2Values[] = $record ? (int) $record->oxygen_saturation : null;
            $glucoseValues[] = $record && isset($record->glucose) ? (int) $record->glucose : null;
            $temperatureValues[] = $record ? (float) $record->temperature : null;
        }

        // 5. Return JSON response
        return response()->json([
            'success' => true,
            'statusCode' => 200,
            'message' => "Vitals trends for the last {$daysCount} days retrieved successfully",
            'data' => [
                'dates' => $dates,
                'metrics' => [
                    'heartRate' => [
                        'unit' => 'bpm',
                        'colorHex' => '#F44336',
                        'values' => $heartRates,
                    ],
                    'bloodPressureSystolic' => [
                        'unit' => 'mmHg',
                        'colorHex' => '#2196F3',
                        'values' => $systolicValues,
                    ],
                    'bloodPressureDiastolic' => [
                        'unit' => 'mmHg',
                        'colorHex' => '#64B5F6',
                        'values' => $diastolicValues,
                    ],
                    'spo2' => [
                        'unit' => '%',
                        'colorHex' => '#00BCD4',
                        'values' => $spo2Values,
                    ],
                    'glucose' => [
                        'unit' => 'mg/dL',
                        'colorHex' => '#9C27B0',
                        'values' => $glucoseValues,
                    ],
                    'temperature' => [
                        'unit' => '°F',
                        'colorHex' => '#FF9800',
                        'values' => $temperatureValues,
                    ],
                ]
            ]
        ], 200);
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
}
