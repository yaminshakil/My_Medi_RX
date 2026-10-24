<?php

namespace App\Http\Controllers\Admin\MenstrualCycle;

use App\Http\Controllers\Controller;
use App\Models\MenstrualCycleLog;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MenstrualCycleLogController extends Controller
{
    public function index(Request $request): Response
    {
        $logs = MenstrualCycleLog::query()
            ->with('profile.user:id,name,email')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->toString();

                $query->whereHas('profile.user', function ($userQuery) use ($search) {
                    $userQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('date'), function ($query) use ($request) {
                $query->whereDate('entry_date', $request->input('date'));
            })
            ->latest('entry_date')
            ->paginate(20)
            ->withQueryString();

        $logs->getCollection()->transform(fn ($log) => [
            'id' => $log->id,
            'uuid' => $log->uuid,
            'user_name' => $log->profile?->user?->name ?? 'Unknown user',
            'email' => $log->profile?->user?->email,
            'entry_date' => $log->entry_date?->format('Y-m-d'),
            'flow' => $log->flow,
            'cramps' => $log->cramps,
            'mood' => $log->mood,
            'water_glasses' => $log->water_glasses,
            'symptoms' => $log->symptoms ?? [],
            'notes' => $log->notes,
        ]);

        return Inertia::render('Admin/MenstrualCycle/Logs/Index', [
            'logs' => $logs,
            'filters' => $request->only('search', 'date'),
        ]);
    }
}
