<?php

namespace App\Http\Controllers\Api\V1\Patient;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class VaccinationController extends Controller
{
    private function patient(Request $request): Patient
    {
        return Patient::where('user_id', $request->user()->id)
            ->firstOrFail();
    }

    public function index(Request $request)
    {
        $records = $this->patient($request)
            ->vaccinationRecords()
            ->orderBy('due_date')
            ->paginate(20);

        return response()->json($records);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'vaccine_name' => ['required', 'string', 'max:255'],
            'dose_number' => ['required', 'integer', 'min:1'],
            'due_date' => ['nullable', 'date'],
            'administered_at' => ['nullable', 'date'],
            'provider' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        $record = $this->patient($request)
            ->vaccinationRecords()
            ->create($data);

        return response()->json([
            'message' => 'Vaccination record created.',
            'data' => $record,
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
            'vaccine_name' => ['sometimes', 'required', 'string', 'max:255'],
            'dose_number' => ['sometimes', 'required', 'integer', 'min:1'],
            'due_date' => ['nullable', 'date'],
            'administered_at' => ['nullable', 'date'],
            'provider' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        $record->update($data);

        return response()->json([
            'message' => 'Vaccination record updated.',
            'data' => $record->fresh(),
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
