<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Notifications\ApiPostActionNotification;
use App\Notifications\PostActionNotification;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class NotifyAdminsOfSuccessfulPost
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($this->shouldNotifyActor($request, $response)) {
            $actor = $request->user();
            $routeAction = $request->route()?->getName() ?? $request->route()?->uri() ?? $request->path();
            $action = Str::headline(str_replace(['api.v1.', 'api/v1/', '.', '/', '{', '}'], ' ', $routeAction));

            if ($actor instanceof User) {
                Notification::send($actor, new ApiPostActionNotification($action));
            }
        }

        if (! $this->shouldNotifyAdmins($request, $response)) {
            return $response;
        }

        $route = $request->route();
        $routeAction = $route?->getName() ?? $route?->uri() ?? $request->path();
        $action = Str::headline(str_replace(['.', '/', '{', '}'], ' ', $routeAction));
        $admins = User::role('Admin')->get();

        Notification::send(
            $admins,
            new PostActionNotification(
                action: $action,
                url: route('dashboard', absolute: false),
            )
        );

        return $response;
    }

    private function shouldNotifyActor(Request $request, Response $response): bool
    {
        return $request->isMethod('post')
            && $response->isSuccessful()
            && ! $request->routeIs('notifications.read', 'api.v1.register', 'api.v1.device-token.store', 'boost.*')
            && ! $request->is('_boost', '_boost/*', 'broadcasting/auth');
    }

    private function shouldNotifyAdmins(Request $request, Response $response): bool
    {
        if (! $request->isMethod('post') || (! $response->isSuccessful() && ! $response->isRedirection())) {
            return false;
        }

        if (
            $request->routeIs('notifications.read', 'api.v1.register', 'api.v1.device-token.store', 'boost.*')
            || $request->is('_boost', '_boost/*', 'broadcasting/auth')
        ) {
            return false;
        }

        if ($request->hasSession()) {
            $newFlashData = $request->session()->get('_flash.new', []);

            if (is_array($newFlashData) && array_intersect(['errors', 'error'], $newFlashData) !== []) {
                return false;
            }
        }

        return true;
    }
}
