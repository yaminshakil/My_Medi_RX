<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\CreateUserRequest;
use App\Http\Requests\Auth\UpdateUserRequest;
use App\Models\PrescriptionMedicine;
use App\Models\Setting;
use App\Models\User;
use App\Services\Auth\UserService;
use App\Services\Doctors\DoctorAnalyticsService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpFoundation\StreamedResponse;

class UserController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $recordsPerPage = Setting::getValue('records_per_page', 10);
        $perPage = $request->input('per_page', $recordsPerPage);
        $search = null;
        $role = null;

        if ($request->filled('search')) {
            $search = $request->search;
        }

        if ($request->filled('role')) {
            $role = $request->role;
        }

        $users = $this->userService->paginate($search, $perPage, $role);

        return Inertia::render('Admin/Users/index', [
            'users'   => $users,
            'roles'   => Role::select('id', 'name')->orderBy('name')->get(),
            'filters' => $request->only(['search', 'per_page', 'role']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Admin/Users/create', [
            'roles' => Role::all(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateUserRequest $request)
    {
        $data = $request->validated();

        $user = $this->userService->create($data);

        event(new Registered($user));

        return redirect()->route('users.index')->with(['success' => 'User Created Successfully']);
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id, DoctorAnalyticsService $analytics): Response
    {
        $user = $this->userService->find($id);
        abort_if($user === null, 404);
        $user->load([
            'roles',
            'doctor',
        ]);

        return Inertia::render('Admin/Users/show', [
            'user'            => $user,
            'doctorAnalytics' => $user->hasRole('Doctor') ? $analytics->dashboard($user) : null,
        ]);
    }

    public function exportDoctorAnalytics(int $id, string $format, DoctorAnalyticsService $analytics): HttpResponse|StreamedResponse
    {
        $user = $this->userService->find($id);
        abort_unless($user?->hasRole('Doctor'), 404);
        abort_unless(in_array($format, ['csv', 'pdf'], true), 404);

        $dashboard = $analytics->dashboard($user);

        if ($format === 'csv') {
            $prescriptions = $analytics->prescriptionLogsQuery($user)
                ->with([
                    'patient:id,name,patient_number',
                    'medications.medicine:id,brand_name,generic_name,strength',
                ])
                ->lazy(500);

            return response()->streamDownload(function () use ($dashboard, $prescriptions): void {
                $output = fopen('php://output', 'wb');

                if ($output === false) {
                    throw new \RuntimeException('Unable to open the report output stream.');
                }

                fputcsv($output, ['Section', 'Date', 'Patient', 'Patient number', 'Prescription number', 'Diagnosis', 'Medicines', 'Visit type', 'Status'], ',', '"', '');
                fputcsv($output, ['Daily analytics', today()->toDateString(), 'Patients', $dashboard['daily']['patients']], ',', '"', '');
                fputcsv($output, ['Daily analytics', today()->toDateString(), 'Prescriptions issued', $dashboard['daily']['prescriptions']], ',', '"', '');
                fputcsv($output, ['Daily analytics', today()->toDateString(), 'New visits', $dashboard['daily']['new_visits']], ',', '"', '');
                fputcsv($output, ['Daily analytics', today()->toDateString(), 'Follow-up visits', $dashboard['daily']['follow_up_visits']], ',', '"', '');
                fputcsv($output, ['Monthly analytics', now()->format('Y-m'), 'Patients', $dashboard['monthly']['patients']], ',', '"', '');
                fputcsv($output, ['Monthly analytics', now()->format('Y-m'), 'Prescriptions issued', $dashboard['monthly']['prescriptions']], ',', '"', '');
                fputcsv($output, ['Monthly analytics', now()->format('Y-m'), 'New visits', $dashboard['monthly']['new_visits']], ',', '"', '');
                fputcsv($output, ['Monthly analytics', now()->format('Y-m'), 'Follow-up visits', $dashboard['monthly']['follow_up_visits']], ',', '"', '');
                fputcsv($output, ['30-day adherence', '', 'Eligible doses', $dashboard['adherence']['eligible_doses']], ',', '"', '');
                fputcsv($output, ['30-day adherence', '', 'Taken', $dashboard['adherence']['taken']], ',', '"', '');
                fputcsv($output, ['30-day adherence', '', 'Missed', $dashboard['adherence']['missed']], ',', '"', '');
                fputcsv($output, ['30-day adherence', '', 'Skipped', $dashboard['adherence']['skipped']], ',', '"', '');
                fputcsv($output, ['30-day adherence', '', 'Compliance percentage', $dashboard['adherence']['percentage'] ?? 'Not available'], ',', '"', '');

                foreach ($prescriptions as $prescription) {
                    $medications = $prescription->medications
                        ->map(static function (PrescriptionMedicine $medication): string {
                            $name = $medication->medicine?->brand_name
                                ?? $medication->medicine?->generic_name
                                ?? 'Medicine';

                            return trim($name.' '.$medication->strength.' '.$medication->dosage);
                        })
                        ->implode('; ');

                    fputcsv($output, [
                        'Prescription log',
                        $prescription->created_at?->toDateTimeString(),
                        $this->spreadsheetSafe($prescription->patient?->name),
                        $this->spreadsheetSafe($prescription->patient?->patient_number),
                        $this->spreadsheetSafe($prescription->prescription_number),
                        $this->spreadsheetSafe($prescription->diagnosis),
                        $this->spreadsheetSafe($medications),
                        $prescription->is_followup ? 'Follow-up' : 'New',
                        $prescription->status,
                    ], ',', '"', '');
                }

                fclose($output);
            }, 'doctor-'.$user->id.'-clinical-report.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
        }

        $prescriptions = $analytics->prescriptionLogsQuery($user)
            ->with([
                'patient:id,name,patient_number',
                'medications.medicine:id,brand_name,generic_name,strength',
            ])
            ->limit(100)
            ->get();

        $pdf = Pdf::loadView('reports.doctor-analytics', [
            'doctor'        => $user,
            'profile'       => $user->doctor,
            'dashboard'     => $dashboard,
            'prescriptions' => $prescriptions,
        ]);

        return $pdf->download('doctor-'.$user->id.'-clinical-report.pdf');
    }

    private function spreadsheetSafe(?string $value): ?string
    {
        if ($value !== null && preg_match('/^[\s\x00-\x1f]*[=+\-@]/u', $value) === 1) {
            return "'".$value;
        }

        return $value;
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $user = $this->userService->find($id);

        return Inertia::render('Admin/Users/edit', [
            'user'      => $user,
            'userRoles' => $user->roles()->pluck('name'),
            'roles'     => Role::all(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        $data = $request->validated();

        $user = $this->userService->update($data, $user);

        return redirect()->route('users.index')->with(['success' => 'User Updated Successfully']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $user = $this->userService->delete($id);

        return redirect()->route('users.index')->with(['success' => 'User Deleted Successfully']);
    }
}
