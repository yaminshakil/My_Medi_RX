<?php

namespace App\Http\Controllers\Api\V1\Patient;

use App\Http\Controllers\Controller;
use App\Models\MedicationSchedule;
use App\Models\Patient;
use App\Models\PatientMedication;
use Illuminate\Http\Request;

class MedicationScheduleController extends Controller
{
    private function patient(Request $request): Patient
    {
        return Patient::where('user_id', $request->user()->id)
            ->firstOrFail();
    }

    public function index(Request $request, PatientMedication $medication)
    {
        $this->authorizeMedication($request, $medication);

        return response()->json([
            'data' => $medication->schedules()
                ->orderBy('scheduled_time')
                ->get(),
        ]);
    }

    public function store(Request $request, PatientMedication $medication)
    {
        $this->authorizeMedication($request, $medication);

        abort_unless(
            $medication->status === 'active',
            422,
            'Only active medications can have new schedules.'
        );

        $data = $request->validate([
            'scheduled_time' => ['required', 'date_format:H:i'],
            'dose_amount'    => ['required', 'numeric', 'gt:0'],
            'dose_unit'      => ['required', 'string', 'max:50'],
            'start_date'     => ['required', 'date'],
            'end_date'       => ['nullable', 'date', 'after_or_equal:start_date'],
            'days_of_week'   => ['nullable', 'array', 'min:1'],
            'days_of_week.*' => [
                'required', 'integer', 'between:0,6',
            ],
        ]);

        $schedule = $medication->schedules()->create([
            ...$data,
            'is_active' => true,
        ]);

        app(\App\Services\Patients\MedicationLogGenerator::class)->generate(
            $schedule,
            now()->toDateString(),
            now()->addDays(6)->toDateString()
        );

        // Generate dose logs through a dedicated service/job.
        // Do not create duplicate logs when schedules are edited.

        return response()->json([
            'message' => 'Medication schedule created.',
            'data'    => $schedule->load('medication'),
        ], 201);
    }

    public function update(
        Request $request,
        MedicationSchedule $schedule
    ) {
        $this->authorizeSchedule($request, $schedule);

        $data = $request->validate([
            'scheduled_time' => ['sometimes', 'date_format:H:i'],
            'dose_amount'    => ['sometimes', 'numeric', 'gt:0'],
            'dose_unit'      => ['sometimes', 'string', 'max:50'],
            'start_date'     => ['sometimes', 'date'],
            'end_date'       => ['nullable', 'date'],
            'days_of_week'   => ['nullable', 'array'],
            'days_of_week.*' => ['integer', 'between:0,6'],
            'is_active'      => ['sometimes', 'boolean'],
        ]);

        $schedule->update($data);

        return response()->json([
            'message' => 'Schedule updated.',
            'data'    => $schedule->fresh(),
        ]);
    }

    public function destroy(
        Request $request,
        MedicationSchedule $schedule
    ) {
        $this->authorizeSchedule($request, $schedule);

        // Preserve logs already recorded against this schedule.
        $schedule->update(['is_active' => false]);

        return response()->json([
            'message' => 'Schedule deactivated.',
        ]);
    }

    private function authorizeMedication(
        Request $request,
        PatientMedication $medication
    ): void {
        abort_unless(
            $medication->patient_id === $this->patient($request)->id,
            404
        );
    }

    private function authorizeSchedule(
        Request $request,
        MedicationSchedule $schedule
    ): void {
        $schedule->loadMissing('medication');

        abort_unless(
            $schedule->medication &&
            $schedule->medication->patient_id === $this->patient($request)->id,
            404
        );
    }
}
