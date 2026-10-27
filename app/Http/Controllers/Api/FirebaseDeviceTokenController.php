<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreFirebaseDeviceTokenRequest;
use App\Models\FirebaseDeviceToken;
use App\Services\ApiResponseService;
use Illuminate\Http\JsonResponse;

class FirebaseDeviceTokenController extends Controller
{
    public function store(StoreFirebaseDeviceTokenRequest $request): JsonResponse
    {
        $token = $request->validated('token');

        FirebaseDeviceToken::updateOrCreate(
            ['token_hash' => hash('sha256', $token)],
            [
                'user_id' => $request->user()->getAuthIdentifier(),
                'token'   => $token,
            ],
        );

        return ApiResponseService::success([], 'Device token registered.');
    }
}
