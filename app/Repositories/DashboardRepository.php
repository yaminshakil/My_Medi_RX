<?php

namespace App\Repositories;

use App\Interfaces\DashboardRepositoryInterface;
use App\Models\Appointment;
use App\Models\DoctorAssistant;
use App\Models\Manufacturer;
use App\Models\Medicine;
use App\Models\MenstrualCycleProfile;
use App\Models\Patient;
use App\Models\PregnancyProfile;
use App\Models\Prescription;
use App\Models\User;
use App\Services\MenstrualCycle\MenstrualCycleService;
use App\Services\Pregnancy\PregnancyStatusService;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardRepository implements DashboardRepositoryInterface
{
    protected $user;

    protected $doctor_id;

    protected $patient_id;

    public function __construct(
        private PregnancyStatusService $pregnancyStatusService,
        private MenstrualCycleService $menstrualCycleService,
    ) {
        $this->user = Auth::user();

        if ($this->user->hasRole('Doctor')) {
            $this->doctor_id = $this->user->id;
        } elseif ($this->user->hasRole('Assistant')) {
            $this->doctor_id = DoctorAssistant::where('user_id', $this->user->id)
                ->value('doctor_id');
        } elseif ($this->user->hasRole('Patient')) {
            $this->patient_id = $this->user->patient?->id;
        }
    }

    public function getToken()
    {
        $user = User::where('email', $this->user->email)->first();

        return $user->tokens()->get()->pluck('token');
    }

    public function getTotalMedicine()
    {
        return Medicine::where('doctor_id', $this->doctor_id)->count();
    }

    public function getTotalManufacturer()
    {
        return Manufacturer::count();
    }

    // Eprescription dashboard

    public function ePrescriptionDashboardInfo()
    {
        $user_id = $this->doctor_id;
        $totalpatients = Patient::count();
        $totalappoinments = Appointment::where('doctor_id', $user_id)->count();
        $todayappointments = Appointment::where('doctor_id', $user_id)
            ->whereDate('created_at', Carbon::today())->count();
        $todayonlineappointments = Appointment::where('doctor_id', $user_id)
            ->where('appointment_type', 'online')
            ->whereDate('created_at', Carbon::today())->count();
        $todayofflineappointments = Appointment::where('doctor_id', $user_id)
            ->where('appointment_type', 'offline')
            ->whereDate('created_at', Carbon::today())->count();
        $totalprescription = Prescription::where('doctor_id', $user_id)->count();
        $todaytotalprescription = Prescription::where('doctor_id', $user_id)->whereDate('created_at', Carbon::today())->count();
        $todaynewprescriptions = Prescription::where('doctor_id', $user_id)
            ->where('is_followup', 0)
            ->whereDate('created_at', Carbon::today())->count();
        $todayoldprescriptions = Prescription::where('doctor_id', $user_id)
            ->where('is_followup', 1)
            ->whereDate('created_at', Carbon::today())->count();

        $monthlyPrescribes = DB::table('prescriptions')
            ->selectRaw('count(prescriptions.id) as total, MONTHNAME(prescriptions.created_at) as month, year(prescriptions.created_at) as year')
            ->where('prescriptions.doctor_id', $user_id)
            ->groupBy('month', 'year')
            ->orderByRaw('MIN(prescriptions.created_at)')
            ->get();

        $dailyPrescription = DB::table('prescriptions')
            ->selectRaw('count(prescriptions.id) as daily_total, DATE(prescriptions.created_at) as prescription_date')
            ->where('prescriptions.doctor_id', $user_id)
            ->whereBetween('prescriptions.created_at', [now()->subDays(30)->startOfDay(), now()->endOfDay()])
            ->groupBy('prescription_date')
            ->orderBy('prescription_date', 'ASC')
            ->get();

        $period = CarbonPeriod::create(now()->subDays(30)->startOfDay(), now()->endOfDay()); // Same date range as above
        $allDates = collect($period)->map(fn ($date) => $date->format('Y-m-d'));

        $dailyPrescriptionchartData = $allDates->mapWithKeys(function ($date) use ($dailyPrescription) {
            $prescription = $dailyPrescription->firstWhere('prescription_date', $date);

            return [$date => $prescription ? $prescription->daily_total : 0];
        });

        $dailyPrescriptionlabels = $dailyPrescriptionchartData->keys()->toArray();
        $dailyPrescriptiondataset = $dailyPrescriptionchartData->values()->toArray();

        $toptenmedicines = Medicine::select(
            'medicines.brand_name',
            'manufacturers.company_name',
            DB::raw('COUNT(prescription_medicines.id) as total_used')
        )
            ->join('manufacturers', 'manufacturers.id', '=', 'medicines.manufacturer_id')
            ->join('prescription_medicines', 'prescription_medicines.medicine_id', '=', 'medicines.id')
            ->join('prescriptions', 'prescriptions.id', '=', 'prescription_medicines.prescription_id')
            ->where('prescriptions.doctor_id', $user_id)
            ->groupBy('medicines.id', 'medicines.brand_name', 'manufacturers.company_name')
            ->orderByDesc('total_used')
            ->take(10)
            ->get()->map(function ($item, $index) {
                $item->id = $index + 1; // 👈 add incremented id starting from 1

                return $item;
            });

        $toptencompanies = Medicine::select(
            'manufacturers.company_name',
            DB::raw('COUNT(prescription_medicines.id) as total_used')
        )
            ->join('manufacturers', 'manufacturers.id', '=', 'medicines.manufacturer_id')
            ->join('prescription_medicines', 'prescription_medicines.medicine_id', '=', 'medicines.id')
            ->join('prescriptions', 'prescriptions.id', '=', 'prescription_medicines.prescription_id')
            ->where('prescriptions.doctor_id', $user_id)
            ->groupBy('manufacturers.id', 'manufacturers.company_name')
            ->orderByDesc('total_used')
            ->take(10)
            ->get()
            ->map(function ($item, $index) {
                $item->id = $index + 1; // add incremented ID for display

                return $item;
            });

        return collect([
            'totalmedicine'          => $this->getTotalMedicine(),
            'totalpatients'          => $totalpatients,
            'totalprescription'      => $totalprescription,
            'todaytotalprescription' => $todaytotalprescription,
            'todaynewprescriptions'  => $todaynewprescriptions,
            'todayoldprescriptions'  => $todayoldprescriptions,
            'monthlyPrescribes'      => response()->json($monthlyPrescribes),
            'toptenmedicines'        => $toptenmedicines,
            'toptencompanies'        => $toptencompanies,
            'appointments'           => [
                'totalappointments'        => $totalappoinments,
                'todaysappointments'       => $todayappointments,
                'todayonlineappointments'  => $todayonlineappointments,
                'todayofflineappointments' => $todayofflineappointments,
            ],
            'dailyPrescriptionformattedData' => collect(['dailyPrescriptionlabels' => $dailyPrescriptionlabels,
                'dailyPrescriptiondataset'                                         => $dailyPrescriptiondataset,
            ]),
        ]);
    }

    // Admin Dashboard

    public function getAdminDashboardInfo()
    {
        $totalpatients = Patient::count();
        $totalappoinments = Appointment::count();

        $todayappointments = Appointment::whereDate('created_at', Carbon::today())->count();

        $todayonlineappointments = Appointment::where('appointment_type', 'online')
            ->whereDate('created_at', Carbon::today())->count();

        $todayofflineappointments = Appointment::where('appointment_type', 'offline')
            ->whereDate('created_at', Carbon::today())->count();

        $totalprescription = Prescription::count();

        $todaytotalprescription = Prescription::whereDate('created_at', Carbon::today())->count();
        $todaynewprescriptions = Prescription::where('is_followup', 0)
            ->whereDate('created_at', Carbon::today())->count();
        $todayoldprescriptions = Prescription::where('is_followup', 1)
            ->whereDate('created_at', Carbon::today())->count();

        $monthlyPrescribes = DB::table('prescriptions')
            ->selectRaw('count(prescriptions.id) as total, MONTHNAME(prescriptions.created_at) as month, year(prescriptions.created_at) as year')->groupBy('month', 'year')
            ->orderByRaw('MIN(prescriptions.created_at)')
            ->get();

        $dailyPrescription = DB::table('prescriptions')
            ->selectRaw('count(prescriptions.id) as daily_total, DATE(prescriptions.created_at) as prescription_date')
            ->whereBetween('prescriptions.created_at', [now()->subDays(30)->startOfDay(), now()->endOfDay()])
            ->groupBy('prescription_date')
            ->orderBy('prescription_date', 'ASC')
            ->get();

        $period = CarbonPeriod::create(now()->subDays(30)->startOfDay(), now()->endOfDay()); // Same date range as above
        $allDates = collect($period)->map(fn ($date) => $date->format('Y-m-d'));

        $dailyPrescriptionchartData = $allDates->mapWithKeys(function ($date) use ($dailyPrescription) {
            $prescription = $dailyPrescription->firstWhere('prescription_date', $date);

            return [$date => $prescription ? $prescription->daily_total : 0];
        });

        $dailyPrescriptionlabels = $dailyPrescriptionchartData->keys()->toArray();
        $dailyPrescriptiondataset = $dailyPrescriptionchartData->values()->toArray();

        $toptenmedicines = Medicine::select(
            'medicines.brand_name',
            'manufacturers.company_name',
            DB::raw('COUNT(prescription_medicines.id) as total_used')
        )
            ->join('manufacturers', 'manufacturers.id', '=', 'medicines.manufacturer_id')
            ->join('prescription_medicines', 'prescription_medicines.medicine_id', '=', 'medicines.id')
            ->join('prescriptions', 'prescriptions.id', '=', 'prescription_medicines.prescription_id')
            ->groupBy('medicines.id', 'medicines.brand_name', 'manufacturers.company_name')
            ->orderByDesc('total_used')
            ->take(10)
            ->get()->map(function ($item, $index) {
                $item->id = $index + 1; // 👈 add incremented id starting from 1

                return $item;
            });

        $toptencompanies = Medicine::select(
            'manufacturers.company_name',
            DB::raw('COUNT(prescription_medicines.id) as total_used')
        )
            ->join('manufacturers', 'manufacturers.id', '=', 'medicines.manufacturer_id')
            ->join('prescription_medicines', 'prescription_medicines.medicine_id', '=', 'medicines.id')
            ->join('prescriptions', 'prescriptions.id', '=', 'prescription_medicines.prescription_id')
            ->groupBy('manufacturers.id', 'manufacturers.company_name')
            ->orderByDesc('total_used')
            ->take(10)
            ->get()
            ->map(function ($item, $index) {
                $item->id = $index + 1; // add incremented ID for display

                return $item;
            });

        $dailyrawdata = DB::table('prescriptions')
            ->join('users', 'prescriptions.doctor_id', '=', 'users.id')
            ->select(
                DB::raw('DATE(prescriptions.created_at) as date'),
                'users.id as doctor_id',
                'users.name as doctor_name',
                DB::raw('COUNT(prescriptions.id) as total')
            )
            ->groupBy(
                DB::raw('DATE(prescriptions.created_at)'),
                'users.id',
                'users.name'
            )
            ->orderBy('date')
            ->get();

        $dailychartData = $this->transformPrescriptionData($dailyrawdata);

        return collect([
            'totalpatients'          => $totalpatients,
            'totalprescription'      => $totalprescription,
            'todaytotalprescription' => $todaytotalprescription,
            'todaynewprescriptions'  => $todaynewprescriptions,
            'todayoldprescriptions'  => $todayoldprescriptions,
            'monthlyPrescribes'      => response()->json($monthlyPrescribes),
            'toptenmedicines'        => $toptenmedicines,
            'toptencompanies'        => $toptencompanies,
            'appointments'           => [
                'totalappointments'        => $totalappoinments,
                'todaysappointments'       => $todayappointments,
                'todayonlineappointments'  => $todayonlineappointments,
                'todayofflineappointments' => $todayofflineappointments,
            ],
            'dailyPrescriptionformattedData' => collect(['dailyPrescriptionlabels' => $dailyPrescriptionlabels,
                'dailyPrescriptiondataset'                                         => $dailyPrescriptiondataset,
            ]),
            'dailychartData' => $dailychartData['data'],
            'doctorsMap'     => $dailychartData['doctors'],
        ]);
    }

    // Eprescription dashboard

    public function patientDashboardInfo(): array
    {
        $patient = $this->user->patient;
        $now = now();
        $today = $now->toDateString();

        $todayLogCounts = $patient
            ? $patient->medicationLogs()
                ->whereDate('scheduled_at', $today)
                ->select('status')
                ->selectRaw('COUNT(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status')
                ->all()
            : [];

        $adherenceLogCounts = $patient
            ? $patient->medicationLogs()
                ->whereBetween('scheduled_at', [
                    $now->copy()->subDays(29)->startOfDay(),
                    $now,
                ])
                ->whereIn('status', ['taken', 'missed', 'skipped'])
                ->select('status')
                ->selectRaw('COUNT(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status')
                ->all()
            : [];

        $overdueUpcomingCount = $patient
            ? $patient->medicationLogs()
                ->whereDate('scheduled_at', $today)
                ->where('status', 'upcoming')
                ->where('scheduled_at', '<', $now)
                ->count()
            : 0;

        if ($overdueUpcomingCount > 0) {
            $todayLogCounts['missed'] = (int) ($todayLogCounts['missed'] ?? 0)
                + $overdueUpcomingCount;
            $todayLogCounts['upcoming'] = max(
                0,
                (int) ($todayLogCounts['upcoming'] ?? 0) - $overdueUpcomingCount
            );
        }

        $todayTaken = (int) ($todayLogCounts['taken'] ?? 0);
        $todayMissed = (int) ($todayLogCounts['missed'] ?? 0);
        $todayUpcoming = (int) ($todayLogCounts['upcoming'] ?? 0);
        $todaySkipped = (int) ($todayLogCounts['skipped'] ?? 0);
        $todaySnoozed = (int) ($todayLogCounts['snoozed'] ?? 0);
        $eligibleDoses = (int) ($adherenceLogCounts['taken'] ?? 0)
            + (int) ($adherenceLogCounts['missed'] ?? 0);
        $adherenceTaken = (int) ($adherenceLogCounts['taken'] ?? 0);
        $latestVital = $patient?->latestVital()->first();

        return [
            'name'                 => $this->user->name ?? $patient?->name ?? 'Patient',
            'totalappoinments'     => $patient?->appointments()->count() ?? 0,
            'newappointments'      => $patient?->appointments()->where('status', 'pending')->count() ?? 0,
            'upcomingAppointments' => $patient?->appointments()
                ->whereDate('appointment_date', '>=', $today)
                ->where('status', '!=', 'completed')
                ->count() ?? 0,
            'totalprescriptions'  => $patient?->prescriptions()->count() ?? 0,
            'activePrescriptions' => $patient?->prescriptions()
                ->where('status', 'issued')
                ->count() ?? 0,
            'medications' => [
                'active' => $patient?->medications()
                    ->where('status', 'active')
                    ->count() ?? 0,
                'today' => [
                    'total'    => array_sum(array_map('intval', $todayLogCounts)),
                    'taken'    => $todayTaken,
                    'missed'   => $todayMissed,
                    'upcoming' => $todayUpcoming,
                    'skipped'  => $todaySkipped,
                    'snoozed'  => $todaySnoozed,
                ],
                'adherence' => [
                    'total'      => $eligibleDoses,
                    'taken'      => $adherenceTaken,
                    'missed'     => $eligibleDoses - $adherenceTaken,
                    'skipped'    => (int) ($adherenceLogCounts['skipped'] ?? 0),
                    'percentage' => $eligibleDoses > 0
                        ? round(($adherenceTaken / $eligibleDoses) * 100, 2)
                        : null,
                ],
            ],
            'vitals' => [
                'recordedAt'       => $latestVital?->created_at?->format('M j, Y'),
                'bloodPressure'    => $latestVital?->blood_pressure,
                'heartRate'        => $latestVital?->heart_rate,
                'oxygenSaturation' => $latestVital?->oxygen_saturation,
                'temperature'      => $latestVital?->temperature,
                'weight'           => $latestVital?->weight,
                'bmi'              => $latestVital?->bmi,
            ],
            'pregnancy' => $this->pregnancyDashboardInfo(),
            'cycle'     => $this->menstrualCycleDashboardInfo(),
        ];
    }

    private function pregnancyDashboardInfo(): array
    {
        $profile = PregnancyProfile::query()
            ->where('user_id', $this->user->id)
            ->first();

        if (! $profile) {
            return ['status' => 'not_started'];
        }

        if (! $profile->is_active) {
            return ['status' => 'inactive'];
        }

        $lastMenstrualPeriod = $profile->last_menstrual_period;

        if (
            ! $lastMenstrualPeriod
            || $lastMenstrualPeriod->isFuture()
            || $lastMenstrualPeriod->diffInDays(today()) > 300
        ) {
            return ['status' => 'needs_review'];
        }

        $status = $this->pregnancyStatusService->getStatus($profile);

        return [
            'status'           => 'active',
            'currentWeek'      => $status['currentWeek'],
            'daysToGo'         => $status['daysToGo'],
            'estimatedDueDate' => $status['estimatedDueDate'],
        ];
    }

    private function menstrualCycleDashboardInfo(): array
    {
        $profile = MenstrualCycleProfile::query()
            ->where('user_id', $this->user->id)
            ->first();

        if (! $profile) {
            return ['status' => 'not_started'];
        }

        if (! $profile->is_active) {
            return ['status' => 'inactive'];
        }

        $status = $this->menstrualCycleService->getStatus($profile);

        return [
            'status'          => 'active',
            'currentCycleDay' => $status['currentCycleDay'],
            'phase'           => $status['phase'],
            'nextPeriod'      => $status['predictions']['nextPeriod']['dateFormatted'] ?? null,
        ];
    }

    public function transformPrescriptionData($raw)
    {
        if ($raw->isEmpty()) {
            return [
                'data'    => [],
                'doctors' => [],
            ];
        }

        // Build unique doctor list: id => name
        $doctorsById = [];
        foreach ($raw as $row) {
            $doctorsById[$row->doctor_id] = $row->doctor_name;
        }

        // date range
        $minDate = Carbon::parse($raw->min('date'));
        $maxDate = Carbon::parse($raw->max('date'));

        // lookup[date][doctor_id] = total
        $lookup = [];
        foreach ($raw as $row) {
            $lookup[$row->date][$row->doctor_id] = (int) $row->total;
        }

        $result = [];
        for ($d = $minDate->copy(); $d->lte($maxDate); $d->addDay()) {
            $dateStr = $d->toDateString();
            $entry = ['date' => $dateStr];

            foreach ($doctorsById as $id => $name) {
                $key = "doctor_{$id}";
                $entry[$key] = $lookup[$dateStr][$id] ?? 0;
            }

            $result[] = $entry;
        }

        // Make doctors map matching keys used in data
        // Option: include id in label if you want legend to disambiguate identical names
        $doctorsMap = [];
        foreach ($doctorsById as $id => $name) {
            // Toggle this string to include the id in legend if desired:
            $doctorsMap["doctor_{$id}"] = $name;                 // "Dr. Ali"
            // $doctorsMap["doctor_{$id}"] = "{$name} (#{$id})"; // "Dr. Ali (#5)"
        }

        return [
            'data'    => $result,
            'doctors' => $doctorsMap,
        ];
    }
}
