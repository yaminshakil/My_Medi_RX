<?php

namespace App\Http\Middleware;

use App\Services\SubscriptionService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveSubscription
{
    public function handle(Request $request, Closure $next, ?string $feature = null): Response
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }
        $service = app(SubscriptionService::class);
        $allowed = $feature ? $service->canUseFeature($user, $feature) : $service->hasActiveSubscription($user);
        if (! $allowed) {
            return $request->expectsJson()
                ? response()->json(['message' => 'An active subscription is required.', 'code' => 'SUBSCRIPTION_REQUIRED'], 402)
                : redirect()->route('subscriptions.index')->with('warning', 'Please choose a subscription plan.');
        }

        return $next($request);
    }
}
