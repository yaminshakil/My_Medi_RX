<?php

namespace App\Http\Controllers\Api\V1\Pregnancy;

use App\Http\Controllers\Controller;
use App\Models\PregnancyProfile;
use App\Services\Pregnancy\PregnancyStatusService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PregnancyStatusController extends Controller
{
    public function __invoke(
        Request $request,
        PregnancyStatusService $service
    ): JsonResponse {
        $profile = PregnancyProfile::query()
            ->where('user_id', $request->user()->id)
            ->first();

        if (! $profile) {
            return response()->json([
                'success'    => false,
                'statusCode' => 404,
                'message'    => 'Pregnancy profile not found.',
                'data'       => null,
            ], 404);
        }

        $data = $service->getStatus($profile);

        return response()->json([
            'success'    => true,
            'statusCode' => 200,
            'message'    => 'Pregnancy details retrieved',
            'data'       => $data,
        ]);
    }
}
