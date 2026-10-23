<?php

namespace App\Http\Controllers\Api\V1\Pregnancy;

use App\Http\Controllers\Controller;
use App\Models\PregnancyProfile;
use App\Models\PregnancySymptomLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PregnancySymptomLogController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $profile = PregnancyProfile::query()
            ->where('user_id', $request->user()->id)
            ->where('is_active', true)
            ->first();

        if (!$profile) {
            return response()->json([
                'success' => false,
                'statusCode' => 404,
                'message' => 'Active pregnancy profile not found.',
                'data' => null,
            ], 404);
        }

        $data = $request->validate([
            'date' => ['required', 'date', 'before_or_equal:today'],
            'symptoms' => ['nullable', 'array', 'max:30'],
            'symptoms.*' => ['string', 'max:100'],
            'mood' => [
                'nullable',
                Rule::in([
                    'Calm',
                    'Happy',
                    'Anxious',
                    'Sad',
                    'Irritable',
                    'Tired',
                    'Other',
                ]),
            ],
            'fetalKickCount' => ['nullable', 'integer', 'min:0', 'max:1000'],
            'waterIntakeLiters' => ['nullable', 'numeric', 'min:0', 'max:20'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $log = PregnancySymptomLog::updateOrCreate(
            [
                'pregnancy_profile_id' => $profile->id,
                'date' => $data['date'],
            ],
            [
                'uuid' => (string) Str::uuid(),
                'symptoms' => $data['symptoms'] ?? [],
                'mood' => $data['mood'] ?? null,
                'fetal_kick_count' => $data['fetalKickCount'] ?? null,
                'water_intake_liters' => $data['waterIntakeLiters'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]
        );

        return response()->json([
            'success' => true,
            'statusCode' => 200,
            'message' => 'Daily symptoms logged successfully',
            'data' => [
                'logId' => $log->uuid,
                'savedAt' => $log->updated_at->toISOString(),
            ],
        ]);
    }

    public function index(Request $request): JsonResponse
    {
        $profile = PregnancyProfile::query()
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$profile) {
            return response()->json([
                'success' => false,
                'statusCode' => 404,
                'message' => 'Pregnancy profile not found.',
                'data' => null,
            ], 404);
        }

        $logs = $profile->symptomLogs()
            ->orderByDesc('date')
            ->paginate(15);

        return response()->json([
            'success' => true,
            'statusCode' => 200,
            'message' => 'Pregnancy symptom logs retrieved',
            'data' => $logs,
        ]);
    }

    public function destroy(Request $request, string $uuid): JsonResponse
    {
        $profile = PregnancyProfile::query()
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $log = $profile->symptomLogs()
            ->where('uuid', $uuid)
            ->firstOrFail();

        $log->delete();

        return response()->json([
            'success' => true,
            'statusCode' => 200,
            'message' => 'Pregnancy symptom log deleted',
            'data' => null,
        ]);
    }
}
