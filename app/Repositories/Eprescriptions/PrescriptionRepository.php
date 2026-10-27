<?php

namespace App\Repositories\Eprescriptions;

use App\Http\Resources\Prescriptions\PrescriptionResource;
use App\Interfaces\Eprescriptions\GynaeHistoryRepositoryInterface;
use App\Interfaces\Eprescriptions\PrescriptionRepositoryInterface;
use App\Jobs\SendFirebasePushNotification;
use App\Models\Medicine;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\PrescriptionMedicine;
use App\Models\User;
use App\Models\Vital;
use App\Notifications\PrescriptionIssuedNotification;
use App\Services\Eprescriptions\PrescriptionNumberService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class PrescriptionRepository implements PrescriptionRepositoryInterface
{
    protected $prescriptionnumber;

    protected $gynaeHistoryRepo;

    protected $user;

    protected $doctor_id;

    protected $patient_id;

    public function __construct(
        PrescriptionNumberService $prescriptionnumber,
        GynaeHistoryRepositoryInterface $gynaeHistoryRepo
    ) {
        $this->prescriptionnumber = $prescriptionnumber;
        $this->gynaeHistoryRepo = $gynaeHistoryRepo;
        $this->user = Auth::user();

        if ($this->user->hasRole('Doctor')) {
            $this->doctor_id = $this->user->id;
        } elseif ($this->user->hasRole('Assistant')) {
            $this->doctor_id = $this->user->doctor_assistant->doctor_id;
        } elseif ($this->user->hasRole('Patient')) {
            $this->patient_id = $this->user->patient?->id;
        } else {
            $this->doctor_id = $this->user->id;
        }
    }

    public function all($filters, $parPage)
    {
        $prescriptions = Prescription::with(['patient', 'doctor', 'doctor.doctor', 'vitals']);

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $prescriptions->whereHas('patient', function ($query) use ($search) {
                $query->where('phone', 'like', "%{$search}%");
            });
        }

        if ($this->doctor_id) {
            $prescriptions = $prescriptions->where('doctor_id', $this->doctor_id);
        } elseif ($this->patient_id) {
            $prescriptions = $prescriptions->where('patient_id', $this->patient_id);
        } else {
            // Fallback: If unauthorized or role cannot be determined, return empty results
            $prescriptions->whereRaw('1 = 0');
        }

        $prescriptions = $prescriptions->latest()->paginate($parPage)->withQueryString();
        $prescriptions->getCollection()->transform(fn ($prescription, $index) => [
            'id'                  => $prescription->id,
            'uuid'                => $prescription->uuid,
            'prescription_number' => $prescription->prescription_number,
            'doctor'              => $prescription->doctor,
            'patient'             => $prescription->patient,
            'vitals'              => $prescription->vitals,
            'status'              => $prescription->status,
            'created_at'          => $prescription->created_at->format('Y-m-d'),
        ]);

        return $prescriptions;
    }

    public function find($id)
    {
        $prescription = Prescription::with(['patient', 'doctor', 'doctor.chamber',
            'doctor.medicine_doses' => function ($query) {
                $query->where('active', true);
            },
            'doctor.medicine_durations' => function ($query) {
                $query->where('is_active', true);
            },
            'vitals', 'medications', 'medications.medicines',
            'gynaeHistory',
        ])->findOrFail($id);
        // dd($prescription->patient->id);
        $vitals = Vital::where('patient_id', $prescription->patient?->id)
            ->latest()
            ->select(
                'id',
                'temperature',
                'blood_pressure',
                'heart_rate',
                'respiratory_rate',
                'oxygen_saturation',
                'weight',
                'height',
                'bmi',
                'notes'
            )->first();

        return [
            'data'   => new PrescriptionResource($prescription),
            'vitals' => $vitals,
        ];
    }

    public function store(array $data)
    {
        DB::beginTransaction();

        try {
            $prescriptionnumber = $this->prescriptionnumber->generate();
            $data += ['prescription_number' => $prescriptionnumber];
            $gynae_history = $data['gynae_history'];
            unset($data['gynae_history']);

            $chamber = $this->user->chamber;
            $data += ['header_left' => $chamber->header_left];
            $data += ['header_right' => $chamber->header_right];
            $data += ['footer_info' => $chamber->footer_info];
            $data += ['chamber_logo' => $chamber->chamber_logo];
            $data += ['chamber_id' => $chamber->id];

            $data += ['doctor_id' => $this->doctor_id];

            $prescription = Prescription::create($data);

            foreach ($data['medications'] as $medicine) {
                PrescriptionMedicine::create([
                    'medicine_id'     => $medicine['medicine_id'],
                    'prescription_id' => $prescription->id,
                    'type'            => $medicine['type'],
                    'strength'        => $medicine['strength'],
                    'dosage'          => $medicine['dosage'],
                    'duration'        => $medicine['duration'],
                    'advice'          => $medicine['advice'],
                    'meal_time'       => $medicine['meal_time'],
                ]);
            }

            // handle gynae history if provided
            if ($gynae_history['marital_status']) {
                $validatedHistory = $this->validateGynaeHistory($gynae_history);
                $validatedHistory['prescription_id'] = $prescription->id;
                $cleaned = array_filter($validatedHistory, function ($value, $key) {
                    if (in_array($key, ['dysmenorrhea', 'contraceptive_use']) && ! $value) {
                        return false; // skip if empty or false
                    }

                    return ! is_null($value) && $value !== '';
                }, ARRAY_FILTER_USE_BOTH);
                $this->gynaeHistoryRepo->create($cleaned);
            }

            $this->notifyPatientOfIssuedPrescription($prescription);

            DB::commit();
            logger()->debug('STEP 6: Transaction committed');

            return $prescription;
        } catch (\Throwable $e) {
            DB::rollBack();

            logger()->error('Prescription store failed', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ]);
            throw $e;
        }
    }

    private function notifyPatientOfIssuedPrescription(Prescription $prescription): void
    {
        if ($prescription->status !== 'issued') {
            return;
        }

        $prescription->loadMissing(['patient.user', 'doctor']);
        $patientUser = $prescription->patient?->user;

        if ($patientUser === null) {
            return;
        }

        $actionRoute = route('api.v1.prescriptions.show', [
            'prescription' => $prescription->id,
        ], absolute: false);
        $doctorName = $prescription->doctor?->name ?? 'your doctor';
        $notification = new PrescriptionIssuedNotification(
            prescriptionId: (string) $prescription->id,
            prescriptionNumber: (string) $prescription->prescription_number,
            doctorName: $doctorName,
            actionRoute: $actionRoute,
        );

        Notification::send($patientUser, $notification);

        foreach ($patientUser->firebaseDeviceTokens()->pluck('id') as $deviceTokenId) {
            SendFirebasePushNotification::dispatch(
                (int) $deviceTokenId,
                'New Prescription',
                "Dr. {$doctorName} issued your prescription {$prescription->prescription_number}.",
                [
                    'type'                => 'prescription_issued',
                    'prescription_id'     => (string) $prescription->id,
                    'prescription_number' => (string) $prescription->prescription_number,
                    'actionRoute'         => $actionRoute,
                ],
            );
        }
    }

    public function delete($id)
    {
        $prescription = Prescription::find($id);

        return $prescription->delete();
    }

    public function getAllMedicine()
    {
        $medicines = Medicine::orderBy('brand_name', 'ASC')->select('id', 'brand_name', 'strength', 'type')->get();

        return $medicines;
    }

    public function saveAndNewPrescription()
    {
        // Dummy doctor & chamber for example
        $doctor = User::with(['medicine_doses', 'medicine_durations'])->where('id', Auth::user()->id)->first();
        $doctor = [
            'id'             => $doctor->id,
            'name'           => $doctor->name,
            'medicine_doses' => $doctor->medicine_doses()->active()->get()->map(function ($query) {
                return [
                    'label' => $query->name,
                    'value' => $query->name,
                ];
            }),
            'medicine_durations' => $doctor->medicine_durations()->active()->orderBy('days')->latest()->get()->map(function ($query) {
                return [
                    'label' => $query->name,
                    'value' => $query->name,
                ];
            }),
            'chamber' => $doctor->chamber,
        ];

        return [
            'doctor' => $doctor,
        ];
    }

    public function getAllPatient()
    {
        $patients = Patient::latest()->get();
        $patients->load(['vital', 'appointment', 'prescriptions' => function ($prescriptionQuery) {
            $prescriptionQuery->where('doctor_id', $this->doctor_id)
                ->latest(); // order prescriptions by latest
        }]);

        $patients = $patients->map(function ($patient) {
            return [
                'id'             => $patient->id,
                'name'           => $patient->name,
                'patient_number' => $patient->patient_number,
                'mobile'         => $patient->phone,
                'vital'          => $patient->vital,
                'prescriptions'  => $patient->prescriptions,
                'appointment'    => $patient->appointment,
                'gender'         => ucfirst(strtolower($patient->gender)),
                'age'            => $patient->date_of_birth->diff(Carbon::now()),
                'current_date'   => Carbon::now()->format('d-M-Y'),
            ];
        });

        return $patients;
    }

    private function validateGynaeHistory(array $data): array
    {
        return validator($data, [
            // Marriage
            'marital_status'    => 'nullable|string',
            'marriage_duration' => 'nullable|string',
            'consanguinity'     => 'nullable|string',

            // Menstrual
            'menarche_age'      => 'nullable|string',
            'lmp'               => 'nullable|date',
            'cycle'             => 'nullable|string',
            'flow'              => 'nullable|string',
            'dysmenorrhea'      => 'nullable|boolean',
            'contraceptive_use' => 'nullable|boolean',

            // Obstetrical
            'gravida'         => 'nullable|integer',
            'para'            => 'nullable|integer',
            'abortion'        => 'nullable|integer',
            'living_children' => 'nullable|integer',

            // Current Pregnancy
            'edd' => 'nullable|date',
            'anc' => 'nullable|string',
            // Notes
            'other_history' => 'nullable|string',
        ])->validate();
    }

    public function getPrescriMedication($id)
    {
        $medicines = PrescriptionMedicine::with(['medicines'])->where('prescription_id', $id)->get();

        return $medicines;
    }
}
