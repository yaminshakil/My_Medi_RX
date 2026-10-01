<?php

namespace App\Providers;

use App\Interfaces\Eprescriptions\AppointmentRepositoryInterface;
use App\Interfaces\Eprescriptions\DoctorChamberRepositoryInterface;
use App\Interfaces\Eprescriptions\GynaeHistoryRepositoryInterface;
use App\Interfaces\Eprescriptions\ManufacturerRepositoryInterface;
use App\Interfaces\Eprescriptions\MedicineDoseRepositoryInterface;
use App\Interfaces\Eprescriptions\MedicineDurationRepositoryInterface;
use App\Interfaces\Eprescriptions\MedicineRepositoryInterface;
use App\Interfaces\Eprescriptions\PrescriptionRepositoryInterface;
use App\Interfaces\Eprescriptions\VitalRepositoryInterface;
use App\Repositories\Eprescriptions\AppointmentRepository;
use App\Repositories\Eprescriptions\DoctorChamberRepository;
use App\Repositories\Eprescriptions\GynaeHistoryRepository;
use App\Repositories\Eprescriptions\ManufacturerRepository;
use App\Repositories\Eprescriptions\MedicineDoseRepository;
use App\Repositories\Eprescriptions\MedicineDurationRepository;
use App\Repositories\Eprescriptions\MedicineRepository;
use App\Repositories\Eprescriptions\PrescriptionRepository;
use App\Repositories\Eprescriptions\VitalRepository;
use Illuminate\Support\ServiceProvider;

class PrescriptionServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(PrescriptionRepositoryInterface::class, PrescriptionRepository::class);
        $this->app->bind(VitalRepositoryInterface::class, VitalRepository::class);
        $this->app->bind(GynaeHistoryRepositoryInterface::class, GynaeHistoryRepository::class);
        $this->app->bind(MedicineDoseRepositoryInterface::class, MedicineDoseRepository::class);
        $this->app->bind(ManufacturerRepositoryInterface::class, ManufacturerRepository::class);
        $this->app->bind(MedicineDurationRepositoryInterface::class, MedicineDurationRepository::class);
        $this->app->bind(MedicineRepositoryInterface::class, MedicineRepository::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
