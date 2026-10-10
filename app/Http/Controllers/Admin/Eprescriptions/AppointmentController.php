<?php

namespace App\Http\Controllers\Admin\Eprescriptions;

use App\Http\Controllers\Controller;
use App\Http\Requests\Eprescription\CreateAppointmentRequest;
use App\Http\Requests\Eprescription\UpdateAppointmentRequest;
use App\Models\Appointment;
use App\Models\DoctorAssistant;
use App\Models\Patient;
use App\Models\Setting;
use App\Models\User;
use App\Services\Eprescriptions\AppointmentService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class AppointmentController extends Controller
{
    protected $appointmentService;

    protected $user;

    protected $doctor_id;

    protected $patient_id;

    public function __construct(AppointmentService $appointmentService)
    {
        $this->appointmentService = $appointmentService;
        $this->user = Auth::user();

        if ($this->user->hasRole('Doctor')) {
            $this->doctor_id = $this->user->id;
        } elseif ($this->user->hasRole('Assistant')) {
            $this->doctor_id = $this->user->doctor_assistant->doctor_id;
        } elseif ($this->user->hasRole('Patient')) {
            $this->patient_id = $this->user->patient->id;
        }
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $recordsPerPage = Setting::getValue('records_per_page', 10);
        $search = null;
        $date = null;
        $status = null;

        if ($request->filled('search')) {
            $search = $request->search;
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'created_at');
        $sortDirection = $request->input('sort_direction', 'desc');

        $perPage = $request->input('per_page', $recordsPerPage); // default 10

        $appointments = $this->appointmentService->paginate($search, $perPage, $sortBy, $sortDirection, $date, $status);

        return Inertia::render('Admin/Eprescriptions/Appointments/Index', [
            'doctor_id' => $this->doctor_id,
            'appointments' => $appointments,
            'filters' => $request->only(['search', 'per_page', 'sort_by', 'sort_direction']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create($doctor_id, $chamber_id = null)
    {
        // If user is Doctor
        if ($this->user->hasRole('Doctor')) {
            $doctor_id = $this->user->id;
        } elseif ($this->user->hasRole('Assistant')) {
            $doctor_id = DoctorAssistant::where('user_id', $this->user->id)
                ->value('doctor_id');
        }

        $doctor = User::with('chambers')->findOrFail($doctor_id);

        // Fetch booked appointments for this doctor
        $appointments = Appointment::where('doctor_id', $doctor_id)
            ->get(['chamber_id', 'appointment_date', 'appointment_time']);
        $appointments = $appointments->map(function ($appointment) {
            return [
                'chamber_id' => $appointment->chamber_id,
                'appointment_date' => $appointment->appointment_date->format('Y-m-d'),
                'appointment_time' => $appointment->appointment_time->format('H:i:s'),
            ];
        });

        $patients = Patient::all();
        $patients = $patients->map(function ($patient) {
            return [
                'id' => $patient->id,
                'patient_number' => $patient->patient_number,
                'name' => $patient->name,
                'mobile' => $patient->phone,
                'email' => $patient->email,
                'gender' => $patient->gender,
                'blood_group' => $patient->blood_group,
            ];
        });

        if ($this->user->hasRole('Doctor') || $this->user->hasRole('Assistant')) {
            return Inertia::render('Admin/Eprescriptions/Appointments/Create', [
                'doctor' => $doctor,
                // 'patient_id' => $patient_id,
                'patients' => $patients,
                'appointments' => $appointments,
            ]);
        } elseif ($this->user->hasRole('Patient')) {
            if ($chamber_id) {
                $doctor = User::with([
                    'chambers' => function ($query) use ($chamber_id) {
                        $query->where('id', $chamber_id);
                    },
                ])->findOrFail($doctor_id);
            }

            $patients = Patient::where('user_id', Auth::user()->id)->get();
            $patients = $patients->map(function ($patient) {
                return [
                    'id' => $patient->id,
                    'patient_number' => $patient->patient_number,
                    'name' => $patient->name,
                    'mobile' => $patient->phone,
                    'email' => $patient->email,
                    'gender' => $patient->gender,
                    'blood_group' => $patient->blood_group,
                    'relationship' => $patient->relationship,
                ];
            });

            return Inertia::render('Frontend/Eprescription/Appointments/Create', [
                'doctor' => $doctor,
                'patient_id' => $patients->first()['id'] ?? null,
                'patients' => $patients,
                'appointments' => $appointments,
            ]);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateAppointmentRequest $request)
    {
        try {
            $data = $request->validated();
            $appointment = $this->appointmentService->create($data);
            $templateSlug = 'appointment_confirmation';
            $this->appointmentService->appointmentMail($appointment, $templateSlug);

            return redirect()->route('appointments.index')->with('success', 'Appointments booked successfully');
        } catch (\Exception $e) {
            return back()->with('error', 'Doctor does not have a chamber.');
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        // Fetch booked appointments for this doctor
        $appointment = Appointment::with('doctor', 'doctor.chambers', 'chamber', 'patient')->find($id);

        $appointments = Appointment::where('doctor_id', $appointment->doctor_id)
            ->get(['chamber_id', 'appointment_date', 'appointment_time']);

        $appointments = $appointments->map(function ($appointment) {
            return [
                'chamber_id' => $appointment->chamber_id,
                'appointment_date' => $appointment->appointment_date->format('Y-m-d'),
                'appointment_time' => $appointment->appointment_time->format('H:i:s'),
            ];
        });

        if ($this->user->hasRole('Doctor') || $this->user->hasRole('Assistant')) {
            return Inertia::render('Admin/Eprescriptions/Appointments/Edit', [
                'appointment' => $appointment,
                'appointments' => $appointments,
            ]);
        } elseif ($this->user->hasRole('Patient')) {
            return Inertia::render('Frontend/Eprescription/Appointments/Edit', [
                'appointment' => $appointment,
                'appointments' => $appointments,
            ]);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAppointmentRequest $request, string $id)
    {
        $data = $request->validated();
        $this->appointmentService->update($data, $id);

        return redirect()->route('appointments.index')->with('success', 'Appointments updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $this->appointmentService->delete($id);

        return redirect()->route('appointments.index')->with('success', 'Appointments deleted successfully');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function quickAppointment($patient_id)
    {
        try {
            $data = $this->appointmentService->quickAppointment($patient_id);
            $appointment = $this->appointmentService->create($data);
            $templateSlug = 'appointment_confirmation';
            $this->appointmentService->appointmentMail($appointment, $templateSlug);

            return redirect()->route('appointments.index')->with('success', 'Appointments booked successfully');
        } catch (\Exception $e) {
            return back()->with('error', 'Doctor does not have a chamber.');
        }
    }

    /** Get Today Appointment */
    public function getTodaysAppointment(Request $request)
    {
        $recordsPerPage = Setting::getValue('records_per_page', 10);
        $search = null;
        $date = Carbon::today();
        $status = null;

        if ($request->filled('search')) {
            $search = $request->search;
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'created_at');
        $sortDirection = $request->input('sort_direction', 'desc');

        $perPage = $request->input('per_page', $recordsPerPage); // default 10

        $appointments = $this->appointmentService->paginate($search, $perPage, $sortBy, $sortDirection, $date, $status);

        return Inertia::render('Admin/Eprescriptions/Appointments/TodayIndex', [
            'doctor_id' => $this->doctor_id,
            'appointments' => $appointments,
            'filters' => $request->only(['search', 'per_page', 'sort_by', 'sort_direction']),
        ]);
    }

    /** Get New Appointment */
    public function getNewAppointment(Request $request)
    {
        $recordsPerPage = Setting::getValue('records_per_page', 10);
        $search = null;
        $date = null;
        $status = 'pending';

        if ($request->filled('search')) {
            $search = $request->search;
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'created_at');
        $sortDirection = $request->input('sort_direction', 'desc');

        $perPage = $request->input('per_page', $recordsPerPage); // default 10

        $appointments = $this->appointmentService->paginate($search, $perPage, $sortBy, $sortDirection, $date, $status);

        return Inertia::render('Admin/Eprescriptions/Appointments/NewIndex', [
            'doctor_id' => $this->doctor_id,
            'appointments' => $appointments,
            'filters' => $request->only(['search', 'per_page', 'sort_by', 'sort_direction']),
        ]);
    }

    public function changeStatus(Request $request)
    {
        if ($request->filled('status') && $request->filled('appointment_id')) {
            $status = $request->status;
            $appointment = $this->appointmentService->find($request->appointment_id);
            $appointment->update(['status' => $status]);

            $templateSlug = 'appointment_status';
            $this->appointmentService->appointmentMail($appointment, $templateSlug);

            return redirect()->back()->with('success', 'Appointment updated successfully');
        }
    }
}
