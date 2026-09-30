<?php

namespace App\Providers;

use App\Interfaces\Auth\PermissionRepositoryInterface;
use App\Interfaces\Auth\RoleRepositoryInterface;
use App\Interfaces\Auth\UserRepositoryInterface;
use App\Interfaces\DashboardRepositoryInterface;
use App\Interfaces\EmailTemplate\EmailTemplateRepositoryInterface;
use App\Interfaces\MenuRepositoryInterface;
use App\Interfaces\Profile\ProfileRepositoryInterface;
use App\Repositories\Auth\PermissionRepository;
use App\Repositories\Auth\RoleRepository;
use App\Repositories\Auth\UserRepository;
use App\Repositories\DashboardRepository;
use App\Repositories\EmailTemplate\EmailTemplateRepository;
use App\Repositories\MenuRepository;
use App\Repositories\Profile\ProfileRepository;
use Illuminate\Support\ServiceProvider;
use App\Interfaces\Patients\PatientProfileRepositoryInterface;
use App\Interfaces\Patients\PatientRepositoryInterface;
use App\Repositories\Patients\PatientProfileRepository;
use App\Repositories\Patients\PatientRepository;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(DashboardRepositoryInterface::class, DashboardRepository::class);
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(RoleRepositoryInterface::class, RoleRepository::class);
        $this->app->bind(PermissionRepositoryInterface::class, PermissionRepository::class);
        $this->app->bind(MenuRepositoryInterface::class, MenuRepository::class);
        $this->app->bind(EmailTemplateRepositoryInterface::class, EmailTemplateRepository::class);
        $this->app->bind(ProfileRepositoryInterface::class, ProfileRepository::class);
        $this->app->bind(PatientRepositoryInterface::class, PatientRepository::class);
        $this->app->bind(PatientProfileRepositoryInterface::class, PatientProfileRepository::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
