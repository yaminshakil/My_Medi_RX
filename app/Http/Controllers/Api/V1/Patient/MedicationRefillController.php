<?php

namespace App\Http\Controllers\Api\V1\Patient;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Models\PatientMedication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MedicationRefillController extends Controller
{
    private function patient(Request $request): Patient
    {
        return Patient::where('user_id', $request->user()->id)
            ->firstOrFail();
    }

    public function index(Request $request)
    {
        $medications = $this->patient($request)->medications()
            ->where('status', 'active')
            ->whereNotNull('remaining_quantity')
            ->whereColumn(
                'remaining_quantity',
                '<=',
                'refill_threshold'
            )
            ->orderBy('remaining_quantity')
            ->get();

        return response()->json([
            'data' => $medications,
        ]);
    }

    public function store(Request $request, PatientMedication $medication)
    {
        abort_unless(
            $medication->patient_id === $this->patient($request)->id,
            404
        );

        $data = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:100000'],
        ]);

        DB::transaction(function () use ($medication, $data) {
            $locked = PatientMedication::whereKey($medication->id)
                ->lockForUpdate()
                ->firstOrFail();

            $locked->remaining_quantity =
                ($locked->remaining_quantity ?? 0) + $data['quantity'];

            $locked->total_quantity =
                ($locked->total_quantity ?? 0) + $data['quantity'];

            $locked->save();
        });

        return response()->json([
            'message' => 'Medication inventory updated.',
            'data' => $medication->fresh(),
        ]);
    }
}
