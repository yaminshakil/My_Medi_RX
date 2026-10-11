<?php

namespace App\Modules\BloodBank\Providers;

use App\Models\User;
use App\Modules\BloodBank\Console\CheckBloodExpiry;
use App\Modules\BloodBank\Repositories\Contracts;
use App\Modules\BloodBank\Repositories\Eloquent;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;

class BloodBankServiceProvider extends ServiceProvider
{
    /** Interface => implementation. Swap an implementation here (or in tests) without touching services. */
    private array $repositories = [
        Contracts\BloodBagRepositoryInterface::class         => Eloquent\BloodBagRepository::class,
        Contracts\StorageUnitRepositoryInterface::class      => Eloquent\StorageUnitRepository::class,
        Contracts\DonorRepositoryInterface::class            => Eloquent\DonorRepository::class,
        Contracts\HospitalRepositoryInterface::class         => Eloquent\HospitalRepository::class,
        Contracts\PatientRepositoryInterface::class          => Eloquent\PatientRepository::class,
        Contracts\PhysicianRepositoryInterface::class        => Eloquent\PhysicianRepository::class,
        Contracts\EmergencyRequestRepositoryInterface::class => Eloquent\EmergencyRequestRepository::class,
        Contracts\BloodIssueRepositoryInterface::class       => Eloquent\BloodIssueRepository::class,
    ];

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/blood-bank.php', 'blood-bank');

        foreach ($this->repositories as $interface => $implementation) {
            $this->app->bind($interface, $implementation);
        }
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/migrations');
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');

        // Who may use the module: anyone attached to a hospital, or an app admin (is_admin column, if you have one).
        Gate::define('manage-blood-bank', fn (User $user) => ! is_null($user->hospital_id) || (bool) ($user->is_admin ?? false));

        // Private channel for live stock updates. Skipped when broadcasting is not configured at all.
        if (config('broadcasting.default')) {
            Broadcast::channel('blood-bank.stock', fn (User $user) => Gate::forUser($user)->allows('manage-blood-bank'));
        }

        // Expose flash messages to every Inertia page (the layout renders them).
        Inertia::share('flash', fn () => [
            'success' => session('success'),
            'error'   => session('error'),
        ]);

        if ($this->app->runningInConsole()) {
            $this->commands([CheckBloodExpiry::class]);
            $this->publishes([__DIR__.'/../config/blood-bank.php' => config_path('blood-bank.php')], 'blood-bank-config');
        }

        // Daily expiry sweep. Needs the Laravel scheduler running (schedule:work locally, cron / Task Scheduler in production).
        $this->callAfterResolving(Schedule::class, function (Schedule $schedule) {
            $schedule->command('blood-bank:check-expiry')->dailyAt('06:00')->withoutOverlapping();
        });
    }
}
