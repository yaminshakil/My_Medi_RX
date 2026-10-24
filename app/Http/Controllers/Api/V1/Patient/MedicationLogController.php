<?php

namespace App\Http\Controllers\Api\V1\Patient;

use App\Http\Controllers\Controller;
use App\Models\MedicationLog;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MedicationLogController extends Controller
{
    private function patient(Request $request): Patient
    {
        return Patient::where('user_id', $request->user()->id)
            ->firstOrFail();
    }

    private function ownedLog(
        Request $request,
        MedicationLog $log
    ): MedicationLog {
        abort_unless(
            $log->patient_id === $this->patient($request)->id,
            404
        );

        return $log;
    }

    public function index(Request $request)
    {
        $data = $request->validate([
            'date'   => ['nullable', 'date'],
            'from'   => ['nullable', 'date'],
            'to'     => ['nullable', 'date', 'after_or_equal:from'],
            'status' => [
                'nullable',
                Rule::in([
                    'upcoming', 'taken', 'missed', 'skipped', 'snoozed',
                ]),
            ],
            'per_page' => ['nullable', 'integer', 'between:1,100'],
        ]);

        $logs = $this->patient($request)
            ->medicationLogs()
            ->with('medication')
            ->when(
                $data['date'] ?? null,
                fn ($q, $date) => $q->whereDate('scheduled_at', $date)
            )
            ->when(
                $data['from'] ?? null,
                fn ($q, $date) => $q->whereDate('scheduled_at', '>=', $date)
            )
            ->when(
                $data['to'] ?? null,
                fn ($q, $date) => $q->whereDate('scheduled_at', '<=', $date)
            )
            ->when(
                $data['status'] ?? null,
                fn ($q, $status) => $q->where('status', $status)
            )
            ->orderByDesc('scheduled_at')
            ->paginate($data['per_page'] ?? 20);

        return response()->json($logs);
    }

    public function taken(Request $request, MedicationLog $log)
    {
        $log = $this->ownedLog($request, $log);

        if ($log->status === 'taken') {
            return response()->json([
                'message' => 'Dose already recorded as taken.',
                'data'    => $log,
            ]);
        }

        abort_unless(
            in_array($log->status, ['upcoming', 'snoozed'], true),
            422,
            'This dose can no longer be marked as taken.'
        );

        $log->update([
            'status'        => 'taken',
            'taken_at'      => now(),
            'snoozed_until' => null,
        ]);

        // Deduct inventory once, transactionally, in production.
        // Use an inventory transaction/idempotency mechanism to
        // prevent duplicate deductions from concurrent requests.

        return response()->json([
            'message' => 'Dose marked as taken.',
            'data'    => $log->fresh(),
        ]);
    }

    public function skip(Request $request, MedicationLog $log)
    {
        $log = $this->ownedLog($request, $log);

        if (! in_array($log->status, ['upcoming', 'snoozed'], true)) {
            return response()->json([
                'message' => 'This dose cannot be skipped in its current state.',
                'status'  => $log->status, // Optional: helpful for debugging
            ], 422);
        }

        $data = $request->validate([
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $log->update([
            'status'        => 'skipped',
            'notes'         => $data['notes'] ?? null,
            'snoozed_until' => null,
        ]);

        return response()->json([
            'message' => 'Dose marked as skipped.',
            'data'    => $log->fresh(),
        ]);
    }

    public function snooze(Request $request, MedicationLog $log)
    {
        $log = $this->ownedLog($request, $log);

        if (! in_array($log->status, ['upcoming', 'snoozed'], true)) {
            return response()->json([
                'message' => 'Only pending doses can be snoozed.',
                'status'  => $log->status, // Optional: helpful for debugging
            ], 422);
        }

        $data = $request->validate([
            'minutes' => ['required', 'integer', 'between:5,120'],
        ]);

        $until = now()->addMinutes($data['minutes']);

        $log->update([
            'status'        => 'snoozed',
            'snoozed_until' => $until,
        ]);

        // Dispatch/reschedule the notification job here.

        return response()->json([
            'message' => 'Dose reminder snoozed.',
            'data'    => $log->fresh(),
        ]);
    }
}
