<?php

namespace App\Http\Controllers\Api\V1\Patient;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use Illuminate\Http\Request;
use App\Models\Vaccine;

class VaccinationController extends Controller
{
    public function vaccines()
    {
        $vaccines = Vaccine::query()
            ->where('is_active', true)
            ->orderBy('created_at')
            ->get();

        return response()->json($vaccines);
    }

    private function patient(Request $request): Patient
    {
        return Patient::where('user_id', $request->user()->id)
            ->firstOrFail();
    }

    public function index(Request $request)
    {
        $records = $this->patient($request)
            ->vaccinationRecords()
            ->orderBy('scheduled_date')
            ->paginate(20);

        return response()->json($records);
    }


    public function store(Request $request)
    {
        $patient = $this->patient($request);

        $data = $request->validate([
            'vaccine_id' => [
                'required',
                'integer',
                'exists:vaccines,id',
            ],
            'vaccination_schedule_id' => [
                'nullable',
                'integer',
                'exists:vaccination_schedules,id',
            ],
            'dose_number' => [
                'required',
                'integer',
                'min:1',
            ],
            'scheduled_date' => [
                'nullable',
                'date',
            ],
            'administered_at' => [
                'nullable',
                'date',
            ],
            'status' => [
                'sometimes',
                'required',
                'in:scheduled,completed,missed,cancelled',
            ],
            'provider_name' => [
                'nullable',
                'string',
                'max:255',
            ],
            'facility_name' => [
                'nullable',
                'string',
                'max:255',
            ],
            'batch_number' => [
                'nullable',
                'string',
                'max:255',
            ],
            'notes' => [
                'nullable',
                'string',
            ],
        ]);

        // Ensure the selected schedule belongs to the selected vaccine.
        if (!empty($data['vaccination_schedule_id'])) {
            $scheduleMatches = \App\Models\VaccinationSchedule::query()
                ->whereKey($data['vaccination_schedule_id'])
                ->where('vaccine_id', $data['vaccine_id'])
                ->exists();

            if (!$scheduleMatches) {
                return response()->json([
                    'success' => false,
                    'message' => 'The selected schedule does not belong to this vaccine.',
                ], 422);
            }
        }

        // Do not allow an inactive vaccine to be assigned.
        $vaccineExists = \App\Models\Vaccine::query()
            ->whereKey($data['vaccine_id'])
            ->where('is_active', true)
            ->exists();

        if (!$vaccineExists) {
            return response()->json([
                'success' => false,
                'message' => 'The selected vaccine is unavailable.',
            ], 422);
        }

        if (!empty($data['administered_at'])) {
            $data['status'] = 'completed';
        } else {
            $data['status'] ??= 'scheduled';
        }

        $record = $patient->vaccinationRecords()->create($data);

        return response()->json([
            'success' => true,
            'message' => 'Vaccination record created successfully.',
            'data' => $record->load(['vaccine', 'schedule']),
        ], 201);
    }


    public function show(Request $request, string $record)
    {
        return response()->json([
            'data' => $this->ownedRecord($request, $record),
        ]);
    }

    public function update(Request $request, string $record)
    {
        $record = $this->ownedRecord($request, $record);

        $data = $request->validate([
            'vaccine_name'    => ['sometimes', 'required', 'string', 'max:255'],
            'dose_number'     => ['sometimes', 'required', 'integer', 'min:1'],
            'due_date'        => ['nullable', 'date'],
            'administered_at' => ['nullable', 'date'],
            'provider'        => ['nullable', 'string', 'max:255'],
            'notes'           => ['nullable', 'string'],
        ]);

        $record->update($data);

        return response()->json([
            'message' => 'Vaccination record updated.',
            'data'    => $record->fresh(),
        ]);
    }

    public function destroy(Request $request, string $record)
    {
        $record = $this->ownedRecord($request, $record);
        $record->delete();

        return response()->json([
            'message' => 'Vaccination record deleted.',
        ]);
    }

    private function ownedRecord(Request $request, string $id)
    {
        return $this->patient($request)
            ->vaccinationRecords()
            ->findOrFail($id);
    }
}
