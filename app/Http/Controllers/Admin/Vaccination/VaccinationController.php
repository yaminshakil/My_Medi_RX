<?php

namespace App\Http\Controllers\Admin\Vaccination;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Models\VaccinationSchedule;
use App\Models\Vaccine;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

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
            ->with(['vaccine', 'schedule'])
            ->latest('scheduled_date')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Admin/Vaccination/Records/Index', [
            'records'  => $records,
            'vaccines' => Vaccine::where('is_active', true)
                ->select('id', 'name')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function create(Request $request)
    {
        return Inertia::render('Admin/Vaccination/Records/Create', [
            'vaccines' => Vaccine::where('is_active', true)
                ->select('id', 'name')
                ->orderBy('name')
                ->get(),

            'schedules' => VaccinationSchedule::where('is_active', true)
                ->with('vaccine:id,name')
                ->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'vaccine_id' => [
                'required',
                Rule::exists('vaccines', 'id')->where('is_active', true),
            ],
            'vaccination_schedule_id' => [
                'nullable',
                'integer',
                'exists:vaccination_schedules,id',
            ],
            'dose_number'     => ['required', 'integer', 'min:1'],
            'scheduled_date'  => ['nullable', 'date'],
            'administered_at' => ['nullable', 'date'],
            'status'          => [
                'sometimes',
                'required',
                Rule::in(['scheduled', 'completed', 'missed', 'cancelled']),
            ],
            'provider_name' => ['nullable', 'string', 'max:255'],
            'facility_name' => ['nullable', 'string', 'max:255'],
            'batch_number'  => ['nullable', 'string', 'max:255'],
            'notes'         => ['nullable', 'string'],
        ]);

        $this->validateSchedule($data);

        $data['status'] = ! empty($data['administered_at'])
            ? 'completed'
            : ($data['status'] ?? 'scheduled');

        $this->patient($request)
            ->vaccinationRecords()
            ->create($data);

        return redirect()
            ->route('admin.vaccinations.index')
            ->with('success', 'Vaccination record created successfully.');
    }

    public function edit(Request $request, string $record)
    {
        $record = $this->ownedRecord($request, $record);

        return Inertia::render('Admin/Vaccination/Records/Edit', [
            'record'   => $record,
            'vaccines' => Vaccine::where('is_active', true)
                ->select('id', 'name')
                ->orderBy('name')
                ->get(),
            'schedules' => VaccinationSchedule::where('is_active', true)
                ->with('vaccine:id,name')
                ->get(),
        ]);
    }

    public function update(Request $request, string $record)
    {
        $record = $this->ownedRecord($request, $record);

        $data = $request->validate([
            'vaccine_id' => [
                'required',
                Rule::exists('vaccines', 'id')->where('is_active', true),
            ],
            'vaccination_schedule_id' => [
                'nullable',
                'integer',
                'exists:vaccination_schedules,id',
            ],
            'dose_number'     => ['required', 'integer', 'min:1'],
            'scheduled_date'  => ['nullable', 'date'],
            'administered_at' => ['nullable', 'date'],
            'status'          => [
                'sometimes',
                'required',
                Rule::in(['scheduled', 'completed', 'missed', 'cancelled']),
            ],
            'provider_name' => ['nullable', 'string', 'max:255'],
            'facility_name' => ['nullable', 'string', 'max:255'],
            'batch_number'  => ['nullable', 'string', 'max:255'],
            'notes'         => ['nullable', 'string'],
        ]);

        $this->validateSchedule($data);

        if (! empty($data['administered_at'])) {
            $data['status'] = 'completed';
        }

        $record->update($data);

        return redirect()
            ->route('admin.vaccinations.index')
            ->with('success', 'Vaccination record updated successfully.');
    }

    public function destroy(Request $request, string $record)
    {
        $this->ownedRecord($request, $record)->delete();

        return back()->with('success', 'Vaccination record deleted successfully.');
    }

    private function ownedRecord(Request $request, string $id)
    {
        return $this->patient($request)
            ->vaccinationRecords()
            ->with(['vaccine', 'schedule'])
            ->findOrFail($id);
    }

    private function validateSchedule(array $data): void
    {
        if (empty($data['vaccination_schedule_id'])) {
            return;
        }

        validator($data, [
            'vaccination_schedule_id' => [
                Rule::exists('vaccination_schedules', 'id')
                    ->where('vaccine_id', $data['vaccine_id'])
                    ->where('is_active', true),
            ],
        ])->validate();
    }
}
