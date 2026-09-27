<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleRedirectMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = Auth::user();

        if (! $user) {
            return redirect()->route('login');
        }

        // Check if user has one of the allowed roles
        if (! $user->hasAnyRole($roles)) {
            return redirect()->route('dashboard')  // 👈 Redirect instead of 403
                ->with('error', 'You are not authorized to access this page.');
        }

        return $next($request);
    }
}
