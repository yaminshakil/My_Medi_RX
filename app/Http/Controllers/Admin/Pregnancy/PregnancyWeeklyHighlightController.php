<?php

namespace App\Http\Controllers\Admin\Pregnancy;

use App\Http\Controllers\Controller;
use App\Models\PregnancyWeeklyHighlight;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PregnancyWeeklyHighlightController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Pregnancy/Highlights/Index', [
            'highlights' => PregnancyWeeklyHighlight::query()
                ->orderBy('week')
                ->paginate(15)
                ->withQueryString(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Pregnancy/Highlights/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        PregnancyWeeklyHighlight::create($this->validated($request));

        return to_route('admin.pregnancy.highlights.index')
            ->with('success', 'Weekly highlight created successfully.');
    }

    public function edit(PregnancyWeeklyHighlight $highlight): Response
    {
        return Inertia::render('Admin/Pregnancy/Highlights/Edit', [
            'highlight' => $highlight,
        ]);
    }

    public function update(
        Request $request,
        PregnancyWeeklyHighlight $highlight
    ): RedirectResponse {
        $highlight->update($this->validated($request, $highlight));

        return to_route('admin.pregnancy.highlights.index')
            ->with('success', 'Weekly highlight updated successfully.');
    }

    public function destroy(
        PregnancyWeeklyHighlight $highlight
    ): RedirectResponse {
        $highlight->delete();

        return to_route('admin.pregnancy.highlights.index')
            ->with('success', 'Weekly highlight deleted successfully.');
    }

    private function validated(
        Request $request,
        ?PregnancyWeeklyHighlight $highlight = null
    ): array {
        return $request->validate([
            'week' => [
                'required',
                'integer',
                'between:1,42',
                'unique:pregnancy_weekly_highlights,week,'.($highlight?->id ?? 'NULL'),
            ],
            'fruit_name'          => ['required', 'string', 'max:100'],
            'approx_length_cm'    => ['nullable', 'numeric', 'gt:0', 'max:300'],
            'approx_weight_grams' => ['nullable', 'integer', 'min:1', 'max:10000'],
            'description'         => ['required', 'string', 'max:1000'],
            'source_reference'    => ['nullable', 'string', 'max:1000'],
            'source_version'      => ['nullable', 'string', 'max:100'],
            'is_published'        => ['required', 'boolean'],
        ]);
    }
}
