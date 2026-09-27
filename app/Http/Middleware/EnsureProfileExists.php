<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureProfileExists
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        // if ($user->hasRole('Doctor') && ! $user->doctor) {
        //     return redirect()->route('doctor.profile')->with('info', 'Please complete your profile first.');
        // }

        // if ($user->hasRole('Patient') && ! $user->patient) {
        //     return redirect()->route('patients.profile.create')->with('info', 'Please complete your profile first.');
        // }

        return $next($request);
    }
}
