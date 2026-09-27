<?php

namespace App\Providers;

use App\Interfaces\Doctors\DoctorEducationRepositoryInterface;
use App\Interfaces\Doctors\DoctorExperienceRepositoryInterface;
use App\Interfaces\Doctors\DoctorRepositoryInterface;
use App\Interfaces\MedicalSpecialty\MedicalSpecialtyRepositoryInterface;
use App\Repositories\Doctors\DoctorEducationRepository;
use App\Repositories\Doctors\DoctorExperienceRepository;
use App\Repositories\Doctors\DoctorRepository;
use App\Repositories\MedicalSpecialty\MedicalSpecialtyRepository;
use Illuminate\Support\ServiceProvider;

class DoctorServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(DoctorRepositoryInterface::class, DoctorRepository::class);
        $this->app->bind(DoctorEducationRepositoryInterface::class, DoctorEducationRepository::class);
        $this->app->bind(DoctorExperienceRepositoryInterface::class, DoctorExperienceRepository::class);
        $this->app->bind(MedicalSpecialtyRepositoryInterface::class, MedicalSpecialtyRepository::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
