<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Interfaces\DashboardRepositoryInterface;
use App\Interfaces\Eprescriptions\DoctorChamberRepositoryInterface;
use App\Models\Appointment;
use App\Services\Pharmacy\PharmacyAnalyticsService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DashboardController extends Controller
{
    private DashboardRepositoryInterface $dashboardRepository;

    private DoctorChamberRepositoryInterface $doctorChamberRepository;

    protected $analytics;

    public function __construct(
        DashboardRepositoryInterface $dashboardRepository,
        DoctorChamberRepositoryInterface $doctorChamberRepository,
        PharmacyAnalyticsService $analytics
    ) {
        $this->dashboardRepository = $dashboardRepository;
        $this->doctorChamberRepository = $doctorChamberRepository;
        $this->analytics = $analytics;
    }

    public function index()
    {
        $user = Auth::user();

        if (! $user) {
            abort(403);
        }

        $token = $this->dashboardRepository->getToken();

        if ($user->hasRole('Doctor') || $user->hasRole('Assistant')) {
            $eprescriptions = $this->dashboardRepository->ePrescriptionDashboardInfo();
            $chambers = $this->doctorChamberRepository->all();

            return Inertia::render('Dashboard/EprescriptionDashboard', [
                'token'          => $token,
                'status'         => session('status'),
                'eprescriptions' => $eprescriptions,
                'chambers'       => $chambers,
            ]);
        } elseif ($user->hasRole('Admin')) {
            $eprescriptions = $this->dashboardRepository->getAdminDashboardInfo();

            return Inertia::render('Dashboard/Dashboard', [
                'token'          => $token,
                'status'         => session('status'),
                'eprescriptions' => $eprescriptions,
            ]);
        } elseif ($user->hasRole('Patient')) {
            $patientinfo = $this->dashboardRepository->patientDashboardInfo();

            return Inertia::render('Dashboard/PatientDashboard', [
                'token'       => $token,
                'patientinfo' => $patientinfo,
            ]);
        } elseif ($user->hasRole('Pharmacy')) {
            return Inertia::render(
                'Admin/Pharmacies/Analytics',
                $this->analytics->dashboard($user),
            );
        } elseif ($user->hasRole('Hospital')) {
            $hospital = $user->hospital()
                ->withCount([
                    'chambers',
                    'chambers as active_chambers_count' => fn (Builder $query) => $query->where('is_active', true),
                ])
                ->first();

            if ($hospital === null) {
                return Inertia::render('Dashboard/HospitalDashboard', [
                    'hospital' => null,
                    'stats'    => [
                        'totalDoctors'        => 0,
                        'totalChambers'       => 0,
                        'activeChambers'      => 0,
                        'todayAppointments'   => 0,
                        'pendingAppointments' => 0,
                    ],
                    'chambers' => [],
                ]);
            }

            $chambers = $hospital->chambers()
                ->with('doctor:id,name,first_name,last_name,email')
                ->orderBy('name')
                ->get([
                    'id',
                    'hospital_id',
                    'doctor_id',
                    'name',
                    'city',
                    'address',
                    'fee',
                    'is_active',
                ]);

            $hospitalAppointments = Appointment::query()
                ->whereIn('chamber_id', $hospital->chambers()->select('id'));

            return Inertia::render('Dashboard/HospitalDashboard', [
                'hospital' => [
                    'name'               => $hospital->hospital_name,
                    'address'            => $hospital->address,
                    'email'              => $hospital->email,
                    'phone'              => $hospital->phone_number ?? $hospital->mobile_number,
                    'verificationStatus' => $hospital->verification_status,
                ],
                'stats' => [
                    'totalDoctors'        => $hospital->chambers()->distinct()->count('doctor_id'),
                    'totalChambers'       => $hospital->chambers_count,
                    'activeChambers'      => $hospital->active_chambers_count,
                    'todayAppointments'   => (clone $hospitalAppointments)->whereDate('appointment_date', today())->count(),
                    'pendingAppointments' => (clone $hospitalAppointments)->where('status', 'pending')->count(),
                ],
                'chambers' => $chambers->map(fn ($chamber): array => [
                    'id'       => $chamber->id,
                    'name'     => $chamber->name,
                    'city'     => $chamber->city,
                    'address'  => $chamber->address,
                    'fee'      => $chamber->fee,
                    'isActive' => (bool) $chamber->is_active,
                    'doctor'   => $chamber->doctor === null ? null : [
                        'name'  => $chamber->doctor->name ?: trim($chamber->doctor->first_name.' '.$chamber->doctor->last_name),
                        'email' => $chamber->doctor->email,
                    ],
                ])->values(),
            ]);
        }
    }
}
