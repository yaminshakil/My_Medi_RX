<?php

use App\Http\Controllers\Api\V1\AiAssistant\AiAssistantController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:api')
    ->prefix('ai-assistant')
    ->group(function () {
        Route::post('/chat', [
            AiAssistantController::class,
            'chat',
        ]);

        Route::get('/history/{sessionId}', [
            AiAssistantController::class,
            'history',
        ])->whereUuid('sessionId');
    });
