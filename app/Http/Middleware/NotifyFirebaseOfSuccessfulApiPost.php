<?php

namespace App\Http\Middleware;

use App\Jobs\SendFirebasePushNotification;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class NotifyFirebaseOfSuccessfulApiPost
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! $this->shouldNotify($request, $response)) {
            return $response;
        }

        $user = $request->user();

        if (! $user instanceof User) {
            return $response;
        }

        $deviceTokens = $user->firebaseDeviceTokens()->get(['id']);
        $routeAction = $request->route()?->getName() ?? $request->route()?->uri() ?? $request->path();
        $action = Str::headline(str_replace(['api.v1.', 'api/v1/', '.', '/', '{', '}'], ' ', $routeAction));

        foreach ($deviceTokens as $deviceToken) {
            try {
                SendFirebasePushNotification::dispatch(
                    $deviceToken->id,
                    'Action completed',
                    "The {$action} action completed successfully.",
                    [
                        'type'   => 'api_post_action',
                        'action' => $action,
                    ],
                );
            } catch (Throwable $exception) {
                Log::error('Unable to queue Firebase push notification.', [
                    'user_id'         => $user->getKey(),
                    'device_token_id' => $deviceToken->id,
                    'error'           => $exception->getMessage(),
                ]);
            }
        }

        return $response;
    }

    private function shouldNotify(Request $request, Response $response): bool
    {
        return $request->is('api/*')
            && $request->isMethod('post')
            && $response->isSuccessful()
            && ! $request->routeIs('api.v1.device-token.store');
    }
}
