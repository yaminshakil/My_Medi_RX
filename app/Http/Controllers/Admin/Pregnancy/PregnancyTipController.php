<?php

namespace App\Http\Controllers\Admin\Pregnancy;

use App\Http\Controllers\Controller;
use App\Models\PregnancyTip;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PregnancyTipController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Pregnancy/Tips/Index', [
            'tips' => PregnancyTip::query()
                ->orderBy('sort_order')
                ->paginate(15)
                ->withQueryString(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Pregnancy/Tips/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        PregnancyTip::create($this->validated($request));

        return to_route('admin.pregnancy.tips.index')
            ->with('success', 'Pregnancy tip created successfully.');
    }

    public function edit(PregnancyTip $tip): Response
    {
        return Inertia::render('Admin/Pregnancy/Tips/Edit', [
            'tip' => $tip,
        ]);
    }

    public function update(Request $request, PregnancyTip $tip): RedirectResponse
    {
        $tip->update($this->validated($request));

        return to_route('admin.pregnancy.tips.index')
            ->with('success', 'Pregnancy tip updated successfully.');
    }

    public function destroy(PregnancyTip $tip): RedirectResponse
    {
        $tip->delete();

        return to_route('admin.pregnancy.tips.index')
            ->with('success', 'Pregnancy tip deleted successfully.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'description' => ['required', 'string', 'max:5000'],
            'icon' => ['required', 'string', 'max:60'],
            'color_hex' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'trimester' => ['nullable', 'integer', 'between:1,3'],
            'week_start' => ['required', 'integer', 'between:1,42'],
            'week_end' => ['required', 'integer', 'gte:week_start', 'between:1,42'],
            'sort_order' => ['required', 'integer', 'min:0'],
            'is_published' => ['required', 'boolean'],
            'source_reference' => ['nullable', 'string', 'max:1000'],
        ]);
    }
}
