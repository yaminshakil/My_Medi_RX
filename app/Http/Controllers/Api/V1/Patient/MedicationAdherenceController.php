<?php

namespace App\Http\Controllers\Api\V1\Patient;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class MedicationAdherenceController extends Controller
{
    private function patient(Request $request): Patient
    {
        return Patient::where('user_id', $request->user()->id)
            ->firstOrFail();
    }

    private function logs(Request $request, int $days = 30)
    {
        $from = now()->subDays($days - 1)->startOfDay();

        return $this->patient($request)
            ->medicationLogs()
            ->whereBetween('scheduled_at', [$from, now()])
            ->whereIn('status', ['taken', 'missed', 'skipped'])
            ->get();
    }

    public function summary(Request $request)
    {
        $data = $request->validate([
            'days' => ['nullable', 'integer', 'between:7,365'],
        ]);

        $logs = $this->logs($request, $data['days'] ?? 30);
        $eligible = $logs->whereIn('status', ['taken', 'missed']);
        $taken = $eligible->where('status', 'taken')->count();
        $total = $eligible->count();

        return response()->json([
            'period_days'          => $data['days'] ?? 30,
            'total_eligible_doses' => $total,
            'taken'                => $taken,
            'missed'               => $eligible->where('status', 'missed')->count(),
            'skipped'              => $logs->where('status', 'skipped')->count(),
            'adherence_percentage' => $total
                ? round(($taken / $total) * 100, 2)
                : null,
        ]);
    }

    public function calendar(Request $request)
    {
        $data = $request->validate([
            'from' => ['nullable', 'date'],
            'to'   => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $from = Carbon::parse(
            $data['from'] ?? now()->subDays(29)->toDateString()
        )->startOfDay();

        $to = Carbon::parse(
            $data['to'] ?? now()->toDateString()
        )->endOfDay();

        abort_if(
            $from->diffInDays($to) > 365,
            422,
            'Calendar range cannot exceed 366 days.'
        );

        $logs = $this->patient($request)->medicationLogs()
            ->whereBetween('scheduled_at', [$from, $to])
            ->get()
            ->groupBy(fn ($log) => $log->scheduled_at->toDateString());

        $days = [];

        for ($date = $from->copy(); $date <= $to; $date->addDay()) {
            $items = $logs->get($date->toDateString(), collect());
            $eligible = $items->whereIn('status', ['taken', 'missed']);
            $total = $eligible->count();
            $taken = $eligible->where('status', 'taken')->count();

            $days[] = [
                'date'                 => $date->toDateString(),
                'total'                => $total,
                'taken'                => $taken,
                'missed'               => $eligible->where('status', 'missed')->count(),
                'adherence_percentage' => $total
                    ? round(($taken / $total) * 100, 2)
                    : null,
            ];
        }

        return response()->json(['data' => $days]);
    }

    public function streaks(Request $request)
    {
        $logs = $this->patient($request)->medicationLogs()
            ->whereDate('scheduled_at', '<=', now()->toDateString())
            ->orderByDesc('scheduled_at')
            ->get()
            ->groupBy(fn ($log) => $log->scheduled_at->toDateString());

        $streak = 0;
        $date = now()->startOfDay();

        // Only days with at least one eligible dose count.
        while (true) {
            $items = $logs->get($date->toDateString());

            if (! $items) {
                if ($date->isToday()) {
                    $date->subDay();

                    continue;
                }
                break;
            }

            $eligible = $items->whereIn('status', ['taken', 'missed']);

            if ($eligible->isEmpty() ||
                $eligible->contains('status', 'missed')) {
                break;
            }

            $streak++;
            $date->subDay();
        }

        return response()->json([
            'current_streak_days' => $streak,
        ]);
    }
}
