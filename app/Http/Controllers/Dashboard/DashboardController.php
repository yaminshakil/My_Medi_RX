<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Interfaces\DashboardRepositoryInterface;
use App\Interfaces\Eprescriptions\DoctorChamberRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use App\Services\Pharmacy\PharmacyAnalyticsService;
use Inertia\Inertia;

class DashboardController extends Controller
{
    private DashboardRepositoryInterface $dashboardRepository;

    private DoctorChamberRepositoryInterface $doctorChamberRepository;

    protected $analytics;

    protected $user;

    public function __construct(
        DashboardRepositoryInterface $dashboardRepository,
        DoctorChamberRepositoryInterface $doctorChamberRepository,
        PharmacyAnalyticsService $analytics
    ) {
        $this->dashboardRepository = $dashboardRepository;
        $this->doctorChamberRepository = $doctorChamberRepository;
        $this->analytics = $analytics;
        $this->user = Auth::user();
    }

    public function index()
    {
        $token = $this->dashboardRepository->getToken();

        if ($this->user->hasRole('Doctor') || $this->user->hasRole('Assistant')) {
            $eprescriptions = $this->dashboardRepository->ePrescriptionDashboardInfo();
            $chambers = $this->doctorChamberRepository->all();

            return Inertia::render('Dashboard/EprescriptionDashboard', [
                'token'          => $token,
                'status'         => session('status'),
                'eprescriptions' => $eprescriptions,
                'chambers'       => $chambers,
            ]);
        } elseif ($this->user->hasRole('Admin')) {
            $eprescriptions = $this->dashboardRepository->getAdminDashboardInfo();

            return Inertia::render('Dashboard/Dashboard', [
                'token'          => $token,
                'status'         => session('status'),
                'eprescriptions' => $eprescriptions,
            ]);
        } elseif ($this->user->hasRole('Patient')) {
            $patientinfo = $this->dashboardRepository->patientDashboardInfo();

            return Inertia::render('Dashboard/PatientDashboard', [
                'token'       => $token,
                'patientinfo' => $patientinfo,
            ]);
        } elseif ($this->user->hasRole('Pharmacy')) {
            return Inertia::render('Admin/Pharmacies/Analytics', $this->analytics->dashboard());
        }
    }
}
