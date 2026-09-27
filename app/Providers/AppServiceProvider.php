<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Wrap all DB interactions safely
        try {
            if (Schema::hasTable('settings')) {
                $appSettings = Setting::pluck('meta_value', 'meta_key')->toArray();

                Inertia::share('appSettings', $appSettings);
                View::share('appSettings', $appSettings);

                // Set timezone dynamically
                $timezone = Setting::getValue('timezone', config('app.timezone'));
                config(['app.timezone' => $timezone]);
                date_default_timezone_set($timezone);
            } else {
                Inertia::share('appSettings', []);
                View::share('appSettings', []);
            }
        } catch (\Throwable $e) {
            // Database not ready — likely during first install
            Inertia::share('appSettings', []);
            View::share('appSettings', []);
        }

        // Global admin gate
        Gate::before(function ($user, $ability) {
            if (method_exists($user, 'hasRole') && $user->hasRole('Admin')) {
                return true;
            }
        });
        Vite::prefetch(concurrency: 3);
    }
}
