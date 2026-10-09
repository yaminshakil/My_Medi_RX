<?php

namespace App\Repositories\Eprescriptions;

use App\Helpers\ShortcodeHelper;
use App\Interfaces\Eprescriptions\AppointmentRepositoryInterface;
use App\Mail\AppointmentMail;
use App\Models\Appointment;
use App\Models\EmailTemplate;
use App\Models\Setting;
use App\Models\User;
use App\Services\Eprescriptions\AppointmentNumberService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class AppointmentRepository implements AppointmentRepositoryInterface
{
    protected $appointmentnumber;

    protected $user;

    protected $doctor_id;

    protected $patient_id;

    public function __construct(AppointmentNumberService $appointmentnumber)
    {
        $this->appointmentnumber = $appointmentnumber;

        $this->user = Auth::user();

        if ($this->user->hasRole('Doctor')) {
            $this->doctor_id = $this->user->id;
        } elseif ($this->user->hasRole('Assistant')) {
            $this->doctor_id = $this->user->doctor_assistant->doctor_id;
        } elseif ($this->user->hasRole('Patient')) {
            $this->patient_id = $this->user?->patient?->id;
        }
    }

    public function paginate($search, $perPage, $sortBy, $sortDirection, $date, $status)
    {
        $appointments = Appointment::query()
            ->join('patients', 'appointments.patient_id', '=', 'patients.id')
            ->select('appointments.*', 'patients.name as patient_name', 'patients.city as patient_city');

        if ($search) {
            $appointments->whereHas('patient', function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%");
            });
        }

        if ($this->doctor_id) {
            $appointments = $appointments->where('doctor_id', $this->doctor_id);
            $appointments->with([
                'patient' => function ($patientQuery) {
                    $patientQuery->with(['prescriptions' => function ($prescriptionQuery) {
                        $prescriptionQuery->where('doctor_id', $this->doctor_id)
                            ->latest(); // order prescriptions by latest
                    }]);
                },
                'chamber' => function ($chamberQuery) {
                    $chamberQuery->where('is_active', true); // only active chambers
                },
            ])
                ->whereHas('chamber', function ($query) {
                    $query->where('is_active', true); // ensure only appointments with active chambers
                });
        } elseif ($this->patient_id) {
            $appointments->where('patient_id', $this->patient_id);
            $appointments->with([
                'patient' => function ($patientQuery) {
                    $patientQuery->with(['prescriptions' => function ($prescriptionQuery) {
                        $prescriptionQuery->where('patient_id', $this->patient_id)
                            ->latest(); // order prescriptions by latest
                    }]);
                },
            ]);
        }

        if ($status) {
            $appointments = $appointments->where('status', $status);
        }

        if ($date) {
            $appointments = $appointments->whereDate('appointment_date', $date);
        }

        if (in_array($sortBy, ['appointment_number', 'name', 'appointment_date', 'appointment_time', 'status', 'city', 'created_at'])) {
            if ($sortBy === 'name') {
                $appointments->orderBy('patients.name', $sortDirection);
            } elseif ($sortBy === 'city') {
                $appointments->orderBy('patients.city', $sortDirection);
            } else {
                $appointments->orderBy("appointments.$sortBy", $sortDirection);
            }
        }

        $appointments = $appointments
            ->orderBy('appointment_date', 'desc')
            ->orderBy('appointment_time', 'desc')
            ->paginate($perPage)
            ->withQueryString();

        $appointments->getCollection()->transform(fn ($appointment) => [
            'id' => $appointment->id,
            'doctor' => $appointment->doctor,
            'patient' => $appointment->patient,
            'chamber' => $appointment->chamber,
            'appointment_number' => $appointment->appointment_number,
            'appointment_date' => $appointment->appointment_date->format('Y-m-d'),
            'appointment_time' => $appointment->appointment_time->format('g:i A'),
            'prescriptions' => $appointment?->patient?->prescriptions,
            'vitals' => $appointment->patient?->vitals()->latest()->take(1)->get(),
            'status' => $appointment->status,
            'created_at' => $appointment->created_at->format('Y-m-d'),
        ]);

        return $appointments;
    }

    public function create(array $data): ?Appointment
    {
        $appointmentnumber = $this->appointmentnumber->generate();
        $data += ['appointment_number' => $appointmentnumber];

        $appointment = Appointment::create($data);

        return $appointment;
    }

    public function update(array $data, int $id): int
    {
        $appointment = Appointment::findOrFail($id);

        $appointment->update($data);
        $appointment->save();

        return $id;
    }

    public function delete(int $id): bool
    {
        $appointment = Appointment::findOrFail($id);

        return $appointment->delete();
    }

    public function find(int $id): ?Appointment
    {
        return Appointment::find($id);
    }

    public function quickAppointment($patient_id)
    {
        $doctor = User::with('chamber')->where('id', Auth::user()->id)->first();

        $appointmentnumber = $this->appointmentnumber->generate();

        return $data = [
            'doctor_id' => $doctor->id,
            'patient_id' => $patient_id,
            'chamber_id' => $doctor->chamber?->id,
            'appointment_number' => $appointmentnumber,
            'appointment_date' => Carbon::now()->format('Y-m-d'),
            'appointment_time' => Carbon::now()->format('H:i:s'),
            'appointment_type' => 'offline',
        ];
    }

    public function appointmentMail($appointment, $templateSlug)
    {
        $appointment->load('doctor', 'patient', 'chamber');

        $template = EmailTemplate::where('slug', $templateSlug)->first();

        if ($template) {
            $doctor = $appointment->doctor;
            $patient = $appointment->patient;
            $currency = Setting::getValue('currency');
            $currency_symbol = Setting::getValue('currency_symbol');
            $data = [
                'booking_date' => $appointment->appointment_date->format('Y-m-d'),
                'time_serial' => $appointment->appointment_time->format('h:i:s A'),
                'doctor_name' => $doctor->name,
                'doctor_fees' => $appointment->chamber->fee,
                'site_currency' => $currency,
                'currency_symbol' => $currency_symbol,
                'booking_status' => $appointment->status,
            ];

            $patientData = [
                'patient_name' => $patient->name,
                'patient_email' => $patient->email,
            ];

            $subject = ShortcodeHelper::parse($template->subject, $data);
            $body = ShortcodeHelper::parse($template->body, $data);

            Mail::to($patient->email)->send(new AppointmentMail($subject, $body, $patientData));
        }
    }
}
