<?php

namespace App\Http\Controllers\Api\V1\MenstrualCycle;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreMenstrualCycleLogRequest;
use App\Models\MenstrualCycleProfile;
use App\Services\MenstrualCycle\MenstrualCycleLogService;
use App\Services\MenstrualCycle\MenstrualCycleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MenstrualCycleController extends Controller
{
    public function __construct(
        private readonly MenstrualCycleService $cycleService,
        private readonly MenstrualCycleLogService $logService,
    ) {
    }

    private function profileFor(Request $request): MenstrualCycleProfile
    {
        return MenstrualCycleProfile::firstOrCreate(
            ['user_id' => $request->user()->id],
            [
                'last_period_start' => $request->input('date', now()),
                'cycle_length' => 28,
                'period_length' => 5,
                'is_active' => true,
            ]
        );
    }

    public function status(Request $request): JsonResponse
    {
        $profile = $this->profileFor($request);

        if (!$profile->is_active) {
            return response()->json([
                'success' => false,
                'statusCode' => 403,
                'message' => 'Menstrual cycle tracking is inactive.',
                'data' => null,
            ], 403);
        }

        return response()->json([
            'success' => true,
            'statusCode' => 200,
            'message' => 'Menstrual cycle status calculated',
            'data' => $this->cycleService->getStatus($profile),
        ]);
    }

    public function log(
        StoreMenstrualCycleLogRequest $request
    ): JsonResponse {
        $profile = $this->profileFor($request);

        if (!$profile->is_active) {
            return response()->json([
                'success' => false,
                'statusCode' => 403,
                'message' => 'Menstrual cycle tracking is inactive.',
                'data' => null,
            ], 403);
        }

        $data = $this->logService->save(
            $profile,
            $request->validated()
        );


        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'Cycle data logged successfully',
            'data' => $data,
        ], 201);
    }
}
