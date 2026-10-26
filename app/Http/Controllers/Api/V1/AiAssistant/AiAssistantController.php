<?php

namespace App\Http\Controllers\Api\V1\AiAssistant;

use App\Http\Controllers\Controller;
use App\Http\Requests\AiAssistantChatRequest;
use App\Services\AiAssistant\AiAssistantService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class AiAssistantController extends Controller
{
    public function __construct(
        private readonly AiAssistantService $aiAssistantService
    ) {
    }

    public function chat(
        AiAssistantChatRequest $request
    ): JsonResponse {
        try {
            $data = $this->aiAssistantService->chat(
                $request->user(),
                $request->validated()
            );

            return response()->json([
                'success' => true,
                'statusCode' => 200,
                'message' => 'AI response generated',
                'data' => $data,
            ]);
        } catch (AuthorizationException $exception) {
            return response()->json([
                'success' => false,
                'statusCode' => 403,
                'message' => $exception->getMessage(),
            ], 403);
        } catch (RuntimeException $exception) {
            return response()->json([
                'success' => false,
                'statusCode' => 503,
                'message' => 'AI assistant is temporarily unavailable.',
            ], 503);
        } catch (Throwable $exception) {
            Log::error('AI Assistant provider error', [
        'exception' => $exception::class,
        'message' => $exception->getMessage(),
        'code' => $exception->getCode(),
        'status' => method_exists($exception, 'getStatusCode')
            ? $exception->getStatusCode()
            : null,
        'file' => $exception->getFile(),
        'line' => $exception->getLine(),
    ]);

            return response()->json([
                'success' => false,
                'statusCode' => 500,
                'message' => 'Unable to process the request.',
            ], 500);
        }
    }

    public function history(
        string $sessionId
    ): JsonResponse {
        try {
            $history = $this->aiAssistantService->history(
                request()->user(),
                $sessionId
            );

            return response()->json([
                'success' => true,
                'statusCode' => 200,
                'message' => 'Chat history fetched',
                'data' => $history,
            ]);
        } catch (ModelNotFoundException $exception) {
            return response()->json([
                'success' => false,
                'statusCode' => 404,
                'message' => 'Chat session not found.',
            ], 404);
        }
    }
}
