<?php

namespace App\Services\MenstrualCycle;

use App\Models\MenstrualCycleLog;
use App\Models\MenstrualCycleProfile;
use Illuminate\Support\Str;

class MenstrualCycleLogService
{
    public function save(
        MenstrualCycleProfile $profile,
        array $data
    ): array {
        $log = MenstrualCycleLog::updateOrCreate(
            [
                'menstrual_cycle_profile_id' => $profile->id,
                'entry_date' => $data['date'],
            ],
            [
                'uuid' => (string) Str::uuid(),
                'flow' => $data['flow'],
                'cramps' => $data['cramps'],
                'mood' => $data['mood'] ?? null,
                'water_glasses' => $data['waterGlasses'],
                'symptoms' => $data['symptoms'] ?? [],
                'notes' => $data['notes'] ?? null,
                'period_started' => $data['periodStarted'] ?? false,
            ]
        );

        if ($log->period_started) {
            $profile->update([
                'last_period_start' => $log->entry_date,
            ]);
        }

        $metrics = [
            'Flow: ' . $log->flow,
            'Cramps: ' . $log->cramps,
        ];

        if ($log->mood) {
            $metrics[] = 'Mood: ' . $log->mood;
        }

        $metrics[] = 'Water: ' . $log->water_glasses . ' glasses';

        foreach ($log->symptoms ?? [] as $symptom) {
            $metrics[] = 'Symptom: ' . $symptom;
        }

        return [
            'entryDate' => $log->entry_date->format('Y-m-d'),
            'recordedMetrics' => $metrics,
        ];
    }
}
