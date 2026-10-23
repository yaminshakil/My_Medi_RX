<?php

namespace App\Http\Controllers\Admin\Pregnancy;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Models\PregnancyProfile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class PregnancyProfileController extends Controller
{
    public function index(Request $request)
    {
        $profiles = PregnancyProfile::query()
            ->with('user:id,name,email')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->toString();

                $query->whereHas('user', function ($userQuery) use ($search) {
                    $userQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $profiles->getCollection()->transform(fn ($profile) => [
            'id' => $profile->id,
            'user_id' => $profile->user_id,
            'name' => $profile->user?->name ?? 'Unknown User',
            'email' => $profile->user?->email,
            'last_menstrual_period' => $profile->last_menstrual_period?->format('Y-m-d'),
            'due_date' => $profile->due_date?->format('Y-m-d'),
            'dating_confirmed_date' => $profile->dating_confirmed_date?->format('Y-m-d'),
            'is_active' => (bool) $profile->is_active,
            'created_at' => $profile->created_at?->format('Y-m-d'),
        ]);

        return Inertia::render('Admin/Pregnancy/Profiles/Index', [
            'profiles' => $profiles,
            'filters' => $request->only('search'),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Pregnancy/Profiles/Create', [
            'patients' => $this->patientOptions(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateProfile($request);

        $validated['due_date'] = now()
            ->parse($validated['last_menstrual_period'])
            ->addDays(280)
            ->toDateString();

        PregnancyProfile::create($validated);

        return redirect()
            ->route('admin.pregnancy.profiles.index')
            ->with('success', 'Pregnancy profile created successfully.');
    }

    public function edit(PregnancyProfile $profile)
    {
        return Inertia::render('Admin/Pregnancy/Profiles/Edit', [
            'profile' => $profile->load('user:id,name,email'),
            'patients' => $this->patientOptions(),
        ]);
    }

    public function update(Request $request, PregnancyProfile $profile)
    {
        $validated = $this->validateProfile($request, $profile);

        $validated['due_date'] = now()
            ->parse($validated['last_menstrual_period'])
            ->addDays(280)
            ->toDateString();

        $profile->update($validated);

        return redirect()
            ->route('admin.pregnancy.profiles.index')
            ->with('success', 'Pregnancy profile updated successfully.');
    }

    public function destroy(PregnancyProfile $profile)
    {
        $profile->delete();

        return redirect()
            ->route('admin.pregnancy.profiles.index')
            ->with('success', 'Pregnancy profile deleted successfully.');
    }

    private function validateProfile(
        Request $request,
        ?PregnancyProfile $profile = null
    ): array {
        return $request->validate([
            'user_id' => [
                'required',
                'integer',
                Rule::exists('patients', 'user_id'),
                Rule::unique('pregnancy_profiles', 'user_id')
                    ->ignore($profile?->id),
            ],
            'last_menstrual_period' => [
                'required',
                'date',
                'before_or_equal:today',
            ],
            'dating_confirmed_date' => [
                'nullable',
                'date',
                'before_or_equal:today',
            ],
            'is_active' => ['required', 'boolean'],
        ]);
    }

    private function patientOptions()
    {
        return Patient::query()
            ->with('user:id,name,email')
            ->get(['id', 'user_id', 'name'])
            ->map(fn (Patient $patient) => [
                'user_id' => $patient->user_id,
                'name' => $patient->name
                    ?: $patient->user?->name
                    ?: 'Patient #' . $patient->id,
                'email' => $patient->user?->email,
            ]);
    }
}
