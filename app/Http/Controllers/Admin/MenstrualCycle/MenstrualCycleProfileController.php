<?php

namespace App\Http\Controllers\Admin\MenstrualCycle;

use App\Http\Controllers\Controller;
use App\Models\MenstrualCycleProfile;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;

class MenstrualCycleProfileController extends Controller
{
    public function index(Request $request): Response
    {
        $profiles = MenstrualCycleProfile::query()
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
            'name' => $profile->user?->name ?? 'Unknown user',
            'email' => $profile->user?->email,
            'last_period_start' => $profile->last_period_start?->format('Y-m-d'),
            'cycle_length' => $profile->cycle_length,
            'period_length' => $profile->period_length,
            'is_active' => $profile->is_active,
            'created_at' => $profile->created_at?->format('Y-m-d'),
        ]);

        return Inertia::render('Admin/MenstrualCycle/Profiles/Index', [
            'profiles' => $profiles,
            'filters' => $request->only('search'),
        ]);
    }

    public function edit(MenstrualCycleProfile $profile): Response
    {
        $profile->load('user:id,name,email');

        return Inertia::render('Admin/MenstrualCycle/Profiles/Edit', [
            'profile' => [
                'id' => $profile->id,
                'name' => $profile->user?->name ?? 'Unknown user',
                'email' => $profile->user?->email,
                'last_period_start' => $profile->last_period_start?->format('Y-m-d'),
                'cycle_length' => $profile->cycle_length,
                'period_length' => $profile->period_length,
                'is_active' => $profile->is_active,
            ],
        ]);
    }

    public function update(Request $request, MenstrualCycleProfile $profile): RedirectResponse
    {
        $validated = $request->validate([
            'last_period_start' => [
                'nullable',
                'date_format:Y-m-d',
                'before_or_equal:today',
            ],
            'cycle_length' => ['required', 'integer', 'between:21,45'],
            'period_length' => ['required', 'integer', 'between:1,10'],
            'is_active' => ['required', 'boolean'],
        ]);

        $profile->update($validated);

        return redirect()
            ->route('admin.menstrual-cycle.profiles.index')
            ->with('success', 'Menstrual cycle profile updated successfully.');
    }

    public function destroy(MenstrualCycleProfile $profile): RedirectResponse
    {
        $profile->delete();

        return redirect()
            ->route('admin.menstrual-cycle.profiles.index')
            ->with('success', 'Menstrual cycle profile deleted successfully.');
    }
}
