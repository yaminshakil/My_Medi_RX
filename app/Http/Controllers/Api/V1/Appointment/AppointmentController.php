<?php

namespace App\Http\Controllers\Api\V1\Appointment;

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
use App\Services\ApiResponseService;

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
    public function index()
    {
        //
    }


    /**
     * Show the form for creating a new resource.
     */
    // public function create($doctor_id, $chamber_id = null)
    // {
    //     // If user is Doctor
    //     if ($this->user->hasRole('Doctor')) {
    //         $doctor_id = $this->user->id;
    //     } elseif ($this->user->hasRole('Assistant')) {
    //         $doctor_id = DoctorAssistant::where('user_id', $this->user->id)
    //             ->value('doctor_id');
    //     }

    //     $doctor = User::with('chambers')->findOrFail($doctor_id);

    //     // Fetch booked appointments for this doctor
    //     $appointments = Appointment::where('doctor_id', $doctor_id)
    //         ->get(['chamber_id', 'appointment_date', 'appointment_time']);
    //     $appointments = $appointments->map(function ($appointment) {
    //         return [
    //             'chamber_id' => $appointment->chamber_id,
    //             'appointment_date' => $appointment->appointment_date->format('Y-m-d'),
    //             'appointment_time' => $appointment->appointment_time->format('H:i:s'),
    //         ];
    //     });

    //     $patients = Patient::all();
    //     $patients = $patients->map(function ($patient) {
    //         return [
    //             'id' => $patient->id,
    //             'patient_number' => $patient->patient_number,
    //             'name' => $patient->name,
    //             'mobile' => $patient->phone,
    //             'email' => $patient->email,
    //             'gender' => $patient->gender,
    //             'blood_group' => $patient->blood_group,
    //         ];
    //     });

    //     if ($this->user->hasRole('Doctor') || $this->user->hasRole('Assistant')) {
    //         $appointmentData = [
    //             'doctor' => $doctor,
    //             // 'patient_id' => $patient_id,
    //             'patients' => $patients,
    //             'appointments' => $appointments,
    //         ];
    //         return ApiResponseService::success(
    //             $appointmentData,
    //             'Patients retrieved successfully!'
    //         );
    //     } elseif ($this->user->hasRole('Patient')) {
    //         if ($chamber_id) {
    //             $doctor = User::with([
    //                 'chambers' => function ($query) use ($chamber_id) {
    //                     $query->where('id', $chamber_id);
    //                 },
    //             ])->findOrFail($doctor_id);
    //         }

    //         $patients = Patient::where('user_id', Auth::user()->id)->get();
    //         $patients = $patients->map(function ($patient) {
    //             return [
    //                 'id' => $patient->id,
    //                 'patient_number' => $patient->patient_number,
    //                 'name' => $patient->name,
    //                 'mobile' => $patient->phone,
    //                 'email' => $patient->email,
    //                 'gender' => $patient->gender,
    //                 'blood_group' => $patient->blood_group,
    //                 'relationship' => $patient->relationship,
    //             ];
    //         });

    //         $appointmentData = [
    //             'doctor' => $doctor,
    //             'patient_id' => $patients->first()['id'] ?? null,
    //             'patients' => $patients,
    //             'appointments' => $appointments,
    //         ];
    //         return ApiResponseService::success(
    //             $appointmentData,
    //             'Patients retrieved successfully!'
    //         );
    //     }
    // }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateAppointmentRequest $request)
    {
        try {
            $data = $request->validated();
            $appointment = $this->appointmentService->create($data);
            $templateSlug = 'appointment_confirmation';
            $appointmentData = $this->appointmentService->appointmentMail($appointment, $templateSlug);
            return ApiResponseService::success(
                $appointmentData,
                'Appointments booked successfully!'
            );
        } catch (\Exception $e) {
            return back()->with('error', 'Doctor does not have a chamber.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
