<?php

namespace App\Http\Controllers\Api\V1\Patient;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Models\PatientMedication;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MedicationController extends Controller
{
    private function patient(Request $request): Patient
    {
        return Patient::where(
            'user_id',
            $request->user()->id
        )->firstOrFail();
    }

    public function index(Request $request)
    {
        $medications = $this->patient($request)
            ->medications()
            ->with('schedules')
            ->when(
                $request->filled('status'),
                fn ($q) => $q->where(
                    'status',
                    $request->string('status')->toString()
                )
            )
            ->latest()
            ->paginate(15);

        return response()->json($medications);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'medicine_name' => ['required', 'string', 'max:255'],
            'strength'      => ['nullable', 'string', 'max:100'],
            'dosage'        => ['nullable', 'string', 'max:100'],
            'dosage_unit'   => ['nullable', 'string', 'max:50'],
            'source'        => [
                'sometimes',
                Rule::in([
                    'doctor_prescription',
                    'qr_scan',
                    'ocr_scan',
                    'manual',
                ]),
            ],
            'prescription_medicine_id' => [
                'nullable',
                'integer',
                'exists:prescription_medicines,id',
            ],
            'start_date' => ['required', 'date'],
            'end_date'   => [
                'nullable', 'date', 'after_or_equal:start_date',
            ],
            'total_quantity'     => ['nullable', 'integer', 'min:0'],
            'remaining_quantity' => [
                'nullable', 'integer', 'min:0',
            ],
            'refill_threshold' => [
                'sometimes', 'integer', 'min:0',
            ],
            'instructions' => ['nullable', 'string'],
        ]);

        // Do not trust a client-supplied prescription medicine ID.
        if (! empty($data['prescription_medicine_id'])) {
            $prescription = $this->patient($request)
                ->prescriptionMedicines()
                ->where(
                    'prescription_medicines.id',
                    $data['prescription_medicine_id']
                )
                ->exists();

            abort_unless(
                $prescription,
                403,
                'Prescription medicine does not belong to this patient.'
            );
        }

        $data['source'] ??= 'manual';
        $data['status'] = 'active';

        $medication = $this->patient($request)
            ->medications()
            ->create($data);

        return response()->json([
            'message' => 'Medication created successfully.',
            'data'    => $medication->load('schedules'),
        ], 201);
    }

    public function show(Request $request, PatientMedication $medication)
    {
        $this->authorizeMedication($request, $medication);

        return response()->json([
            'data' => $medication->load('schedules', 'logs'),
        ]);
    }

    public function update(
        Request $request,
        PatientMedication $medication
    ) {
        $this->authorizeMedication($request, $medication);

        $data = $request->validate([
            'medicine_name'      => ['sometimes', 'required', 'string', 'max:255'],
            'strength'           => ['nullable', 'string', 'max:100'],
            'dosage'             => ['nullable', 'string', 'max:100'],
            'dosage_unit'        => ['nullable', 'string', 'max:50'],
            'start_date'         => ['sometimes', 'required', 'date'],
            'end_date'           => ['nullable', 'date'],
            'total_quantity'     => ['nullable', 'integer', 'min:0'],
            'remaining_quantity' => ['nullable', 'integer', 'min:0'],
            'refill_threshold'   => ['sometimes', 'integer', 'min:0'],
            'instructions'       => ['nullable', 'string'],
            'status'             => [
                'sometimes',
                Rule::in(['active', 'paused', 'completed', 'discontinued']),
            ],
        ]);

        $medication->update($data);

        return response()->json([
            'message' => 'Medication updated successfully.',
            'data'    => $medication->fresh(),
        ]);
    }

    public function destroy(
        Request $request,
        PatientMedication $medication
    ) {
        $this->authorizeMedication($request, $medication);

        // Keep historical medication logs for reporting.
        $medication->update(['status' => 'discontinued']);

        return response()->json([
            'message' => 'Medication discontinued successfully.',
        ]);
    }

    public function dashboard(Request $request)
    {
        $patient = Patient::where(
            'user_id',
            $request->user()->id
        )->firstOrFail();

        $today = now()->toDateString();

        $logs = $patient->medicationLogs()
            ->with('medication')
            ->whereDate('scheduled_at', $today)
            ->orderBy('scheduled_at')
            ->get();

        $logs->each(function ($log) {
            if (
                $log->status === 'upcoming' &&
                $log->scheduled_at->isPast()
            ) {
                $log->status = 'missed';
                $log->save();
            }
        });

        return response()->json([
            'success' => true,
            'data' => [
                'medications' => $patient->medications()
                    ->where('status', 'active')
                    ->get(),

                'today' => $logs,

                'summary' => [
                    'total' => $logs->count(),
                    'taken' => $logs->where(
                        'status',
                        'taken'
                    )->count(),
                    'missed' => $logs->where(
                        'status',
                        'missed'
                    )->count(),
                    'upcoming' => $logs->where(
                        'status',
                        'upcoming'
                    )->count(),
                    'snoozed' => $logs->where(
                        'status',
                        'snoozed'
                    )->count(),
                ],
            ],
        ]);
    }

    private function authorizeMedication(
        Request $request,
        PatientMedication $medication
    ): void {
        abort_unless(
            $medication->patient_id === $this->patient($request)->id,
            404,
            'Medication not found.'
        );
    }
}
