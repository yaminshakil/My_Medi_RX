<?php

namespace App\Services\Pregnancy;

use App\Models\PregnancyProfile;
use App\Models\PregnancyTip;
use App\Models\PregnancyWeeklyHighlight;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class PregnancyStatusService
{
    public function getStatus(PregnancyProfile $profile): array
    {
        if (! $profile->is_active) {
            throw ValidationException::withMessages([
                'pregnancy' => ['This pregnancy profile is inactive.'],
            ]);
        }

        $startDate = $profile->last_menstrual_period;

        if (! $startDate) {
            throw ValidationException::withMessages([
                'last_menstrual_period' => [
                    'A last menstrual period date is required to calculate gestational age.',
                ],
            ]);
        }

        $today = Carbon::today();
        $start = Carbon::parse($startDate)->startOfDay();

        if ($start->greaterThan($today)) {
            throw ValidationException::withMessages([
                'last_menstrual_period' => [
                    'The date cannot be in the future.',
                ],
            ]);
        }

        $gestationalDays = $start->diffInDays($today);

        if ($gestationalDays > 300) {
            throw ValidationException::withMessages([
                'last_menstrual_period' => [
                    'The date is outside the supported pregnancy tracking period. Please verify the pregnancy dates.',
                ],
            ]);
        }

        $currentWeek = intdiv((int) $gestationalDays, 7) + 1;
        $currentDay = (int) $gestationalDays % 7;

        // 40 weeks is the conventional estimated pregnancy duration.
        $daysToGo = max(0, 280 - (int) $gestationalDays);
        $progressPercent = round(
            min(100, ((int) $gestationalDays / 280) * 100),
            1
        );

        $highlight = $currentWeek <= 40
            ? PregnancyWeeklyHighlight::query()
                ->where('week', $currentWeek)
                ->where('is_published', true)
                ->first()
            : null;

        $trimester = match (true) {
            $currentWeek <= 13 => 1,
            $currentWeek <= 27 => 2,
            default            => 3,
        };

        $tips = PregnancyTip::query()
            ->where('is_published', true)
            ->where('week_start', '<=', min($currentWeek, 40))
            ->where('week_end', '>=', min($currentWeek, 40))
            ->where(function ($query) use ($trimester) {
                $query->whereNull('trimester')
                    ->orWhere('trimester', $trimester);
            })
            ->orderBy('sort_order')
            ->get()
            ->map(fn (PregnancyTip $tip) => [
                'id'       => (string) $tip->id,
                'title'    => $tip->title,
                'desc'     => $tip->description,
                'icon'     => $tip->icon,
                'colorHex' => $tip->color_hex,
            ])
            ->values();

        return [
            'currentWeek'        => $currentWeek,
            'currentDay'         => $currentDay,
            'daysToGo'           => $daysToGo,
            'progressPercent'    => $progressPercent,
            'estimatedDueDate'   => $start->copy()->addDays(280)->toDateString(),
            'babySizeComparison' => $highlight ? [
                'fruitName'      => $highlight->fruit_name,
                'approxLengthCm' => $highlight->approx_length_cm === null
                    ? null : (float) $highlight->approx_length_cm,
                'approxWeightGrams' => $highlight->approx_weight_grams,
                'description'       => $highlight->description,
            ] : null,
            'weeklyTips' => $tips,
        ];
    }
}
