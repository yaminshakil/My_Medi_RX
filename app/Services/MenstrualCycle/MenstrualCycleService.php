<?php

namespace App\Services\MenstrualCycle;

use App\Models\MenstrualCycleProfile;
use Carbon\Carbon;
use Carbon\CarbonInterface;

class MenstrualCycleService
{
    public function getStatus(
        MenstrualCycleProfile $profile,
        ?CarbonInterface $today = null
    ): array {
        $today = $today
            ? Carbon::parse($today->toDateString())->startOfDay()
            : today()->startOfDay();

        $lastPeriod = $profile->last_period_start
            ? Carbon::parse($profile->last_period_start)->startOfDay()
            : null;

        if (! $lastPeriod || $lastPeriod->greaterThan($today)) {
            return [
                'currentCycleDay'  => null,
                'phase'            => 'Unknown',
                'phaseDescription' => 'Record the first day of your last period to calculate your cycle.',
                'cycleProgress'    => 0,
                'calendarMonth'    => $today->format('F Y'),
                'predictions'      => [
                    'nextPeriod'      => null,
                    'fertilityWindow' => null,
                ],
            ];
        }

        $cycleLength = (int) $profile->cycle_length;
        $periodLength = (int) $profile->period_length;

        // Normalize to the current cycle if a previous cycle has elapsed.
        $daysSinceStart = $lastPeriod->diffInDays($today);
        $cyclesElapsed = intdiv($daysSinceStart, $cycleLength);

        $cycleStart = $lastPeriod->copy()
            ->addDays($cyclesElapsed * $cycleLength);

        $cycleDay = $cycleStart->diffInDays($today) + 1;

        // Estimated ovulation is approximately 14 days before the next period.
        $ovulationDay = max(1, $cycleLength - 14 + 1);

        if ($cycleDay <= $periodLength) {
            $phase = 'Menstrual Phase';
            $description = 'Menstrual phase — period days.';
        } elseif (
            $cycleDay >= max($periodLength + 1, $ovulationDay - 5)
            && $cycleDay < $ovulationDay
        ) {
            $phase = 'Follicular Phase';
            $description = 'Follicular phase — fertility may increase as ovulation approaches.';
        } elseif ($cycleDay === $ovulationDay) {
            $phase = 'Ovulation Phase';
            $description = 'Estimated ovulation day. Ovulation timing can vary.';
        } elseif (
            $cycleDay > $ovulationDay
            && $cycleDay <= min($cycleLength, $ovulationDay + 1)
        ) {
            $phase = 'Ovulation Phase';
            $description = 'Around the estimated ovulation period.';
        } else {
            $phase = 'Luteal Phase';
            $description = 'Luteal phase — the next period may be approaching.';
        }

        $nextPeriod = $cycleStart->copy()->addDays($cycleLength);
        $daysRemaining = max(0, (int) $today->diffInDays($nextPeriod, false));

        $fertilityStart = $cycleStart->copy()
            ->addDays(max(0, $ovulationDay - 6));

        $fertilityEnd = $cycleStart->copy()
            ->addDays(min($cycleLength - 1, $ovulationDay));

        $inFertilityWindow = $today->betweenIncluded(
            $fertilityStart,
            $fertilityEnd
        );

        $fertilityStatus = $inFertilityWindow ? 'High' : 'Estimated';

        return [
            'currentCycleDay'  => $cycleDay,
            'phase'            => $phase,
            'phaseDescription' => $description,
            'cycleProgress'    => round(
                min(1, $cycleDay / $cycleLength),
                2
            ),
            'calendarMonth' => $today->format('F Y'),
            'predictions'   => [
                'nextPeriod' => [
                    'title'         => 'Next Period',
                    'date'          => $nextPeriod->toDateString(),
                    'dateFormatted' => $nextPeriod->format('F d, Y'),
                    'daysRemaining' => $daysRemaining,
                ],
                'fertilityWindow' => [
                    'title'          => 'Fertility Window',
                    'startDate'      => $fertilityStart->toDateString(),
                    'endDate'        => $fertilityEnd->toDateString(),
                    'rangeFormatted' => $fertilityStart->format('M d')
                        .' - '
                        .$fertilityEnd->format('M d'),
                    'status' => $fertilityStatus,
                ],
            ],
        ];
    }
}
