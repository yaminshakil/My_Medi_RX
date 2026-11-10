<?php

namespace App\Http\Controllers\Admin\Vaccination;

use App\Http\Controllers\Controller;
use App\Models\VaccinationSchedule;
use App\Models\Vaccine;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class VaccinationScheduleController extends Controller
{
    public function index(Request $request)
    {
        $schedules = VaccinationSchedule::query()
            ->with('vaccine:id,name')
            ->when(
                $request->search,
                fn ($query, $search) => $query->where(
                    'schedule_name',
                    'like',
                    "%{$search}%"
                )
            )
            ->when(
                $request->vaccine_id,
                fn ($query, $id) => $query->where('vaccine_id', $id)
            )
            ->when(
                $request->is_active !== null,
                fn ($query) => $query->where(
                    'is_active',
                    $request->boolean('is_active')
                )
            )
            ->latest()
            ->paginate($request->integer('per_page', 10))
            ->withQueryString();

        return Inertia::render('Admin/Vaccination/Schedules/Index', [
            'schedules' => $schedules,
            'filters'   => $request->only([
                'search',
                'vaccine_id',
                'is_active',
                'per_page',
            ]),
            'vaccines' => Vaccine::where('is_active', true)
                ->select('id', 'name')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Vaccination/Schedules/Create', [
            'vaccines' => Vaccine::where('is_active', true)
                ->select('id', 'name')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'vaccine_id' => [
                'required',
                'exists:vaccines,id',
                Rule::exists('vaccines', 'id')->where('is_active', true),
            ],
            'schedule_name'        => ['nullable', 'string', 'max:255'],
            'dose_number'          => ['required', 'integer', 'min:1'],
            'recommended_age_days' => ['nullable', 'integer', 'min:0'],
            'interval_days'        => ['nullable', 'integer', 'min:0'],
            'notes'                => ['nullable', 'string'],
            'is_active'            => ['boolean'],
        ]);

        VaccinationSchedule::create($data);

        return redirect()
            ->route('vaccination-schedules.index')
            ->with('success', 'Vaccination schedule created successfully.');
    }

    public function edit(VaccinationSchedule $schedule)
    {
        return Inertia::render('Admin/Vaccination/Schedules/Edit', [
            'schedule' => $schedule->load('vaccine'),
            'vaccines' => Vaccine::where('is_active', true)
                ->select('id', 'name')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function update(Request $request, VaccinationSchedule $schedule)
    {
        $data = $request->validate([
            'vaccine_id' => [
                'required',
                'exists:vaccines,id',
                Rule::exists('vaccines', 'id')->where('is_active', true),
            ],
            'schedule_name'        => ['nullable', 'string', 'max:255'],
            'dose_number'          => ['required', 'integer', 'min:1'],
            'recommended_age_days' => ['nullable', 'integer', 'min:0'],
            'interval_days'        => ['nullable', 'integer', 'min:0'],
            'notes'                => ['nullable', 'string'],
            'is_active'            => ['boolean'],
        ]);

        $schedule->update($data);

        return redirect()
            ->route('vaccination-schedules.index')
            ->with('success', 'Vaccination schedule updated successfully.');
    }

    public function destroy(VaccinationSchedule $schedule)
    {
        $schedule->delete();

        return back()->with('success', 'Vaccination schedule deleted.');
    }
}
