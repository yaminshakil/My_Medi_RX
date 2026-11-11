<?php

namespace App\Modules\BloodBank\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Hospital;
use App\Modules\BloodBank\Enums\BloodComponent;
use App\Modules\BloodBank\Enums\BloodGroup;
use App\Modules\BloodBank\Enums\EmergencyStatus;
use App\Modules\BloodBank\Exceptions\BloodBankException;
use App\Modules\BloodBank\Http\Requests\StorePatientDonorProfileRequest;
use App\Modules\BloodBank\Http\Requests\StorePatientEmergencyRequestRequest;
use App\Modules\BloodBank\Http\Requests\UpdatePatientDonorProfileRequest;
use App\Modules\BloodBank\Models\BloodBag;
use App\Modules\BloodBank\Models\BloodDonor;
use App\Modules\BloodBank\Models\EmergencyRequest;
use App\Modules\BloodBank\Services\EmergencyBroadcastService;
use App\Modules\BloodBank\Support\LookupOptions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PatientBloodBankController extends Controller
{
    public function __construct(private readonly EmergencyBroadcastService $broadcastService) {}

    public function index(Request $request): Response
    {
        $hospitals = Hospital::query()
            ->where('verification_status', 'approved')
            ->whereHas('bloodBankStorageUnits', fn ($query) => $query->where('is_active', true))
            ->orderBy('hospital_name')
            ->get([
                'id',
                'hospital_name',
                'address',
                'mobile_number',
                'phone_number',
                'emergency_contact',
                'latitude',
                'longitude',
            ]);

        $availability = BloodBag::query()
            ->join('bb_storage_units', 'bb_blood_bags.storage_unit_id', '=', 'bb_storage_units.id')
            ->whereNotNull('bb_storage_units.hospital_id')
            ->where('bb_storage_units.is_active', true)
            ->where('bb_blood_bags.status', 'available')
            ->where('bb_blood_bags.expires_at', '>', now())
            ->selectRaw('bb_storage_units.hospital_id, bb_blood_bags.blood_group, bb_blood_bags.component, COUNT(*) AS units')
            ->groupBy('bb_storage_units.hospital_id', 'bb_blood_bags.blood_group', 'bb_blood_bags.component')
            ->get()
            ->groupBy('hospital_id');

        $banks = $hospitals->map(function (Hospital $hospital) use ($availability): array {
            $counts = $availability->get($hospital->id, collect())
                ->mapWithKeys(fn ($row): array => [
                    $row->blood_group->value.'|'.$row->component->value => (int) $row->units,
                ]);
            $stock = [];

            foreach (BloodGroup::values() as $group) {
                foreach (BloodComponent::cases() as $component) {
                    $stock[$group][$component->value] = $counts->get($group.'|'.$component->value, 0);
                }
            }

            return [
                'id'           => $hospital->id,
                'name'         => $hospital->hospital_name,
                'address'      => $hospital->address,
                'phone'        => $hospital->emergency_contact ?: $hospital->mobile_number ?: $hospital->phone_number,
                'latitude'     => $hospital->latitude,
                'longitude'    => $hospital->longitude,
                'availability' => $stock,
            ];
        });

        $requests = EmergencyRequest::query()
            ->with([
                'hospital:id,hospital_name',
                'donors' => fn ($query) => $query->wherePivot('response', 'accepted'),
            ])
            ->where('requested_by', $request->user()->id)
            ->latest('broadcast_at')
            ->limit(10)
            ->get()
            ->map(fn (EmergencyRequest $emergencyRequest): array => [
                'id'           => $emergencyRequest->id,
                'reference'    => $emergencyRequest->reference,
                'hospital'     => $emergencyRequest->hospital?->hospital_name,
                'blood_group'  => $emergencyRequest->blood_group->value,
                'component'    => $emergencyRequest->component->label(),
                'units_needed' => $emergencyRequest->units_needed,
                'status'       => $emergencyRequest->isOpen()
                    ? EmergencyStatus::Open->value
                    : ($emergencyRequest->status === EmergencyStatus::Open
                        ? 'expired'
                        : $emergencyRequest->status->value),
                'donors_notified_count' => $emergencyRequest->donors_notified_count,
                'broadcast_at'          => $emergencyRequest->broadcast_at->toIso8601String(),
                'donors'                => $emergencyRequest->donors
                    ->sortBy(fn ($donor) => $donor->pivot->distance_km)
                    ->map(fn ($donor): array => [
                        'id'          => $donor->id,
                        'name'        => $donor->name,
                        'phone'       => $donor->phone,
                        'blood_group' => $donor->blood_group->value,
                        'distance_km' => (float) $donor->pivot->distance_km,
                    ])->values(),
            ]);

        return Inertia::render('Patient/BloodBank/Index', [
            'banks'    => $banks->values(),
            'requests' => $requests->values(),
            'donor'    => BloodDonor::query()->where('user_id', $request->user()->id)->first([
                'id',
                'name',
                'phone',
                'blood_group',
                'city',
                'is_verified',
                'is_available',
            ]),
            'options' => [
                'groups'     => LookupOptions::groups(),
                'components' => LookupOptions::components(),
                'urgencies'  => LookupOptions::urgencies(),
            ],
            'defaults' => [
                'radius_km'     => config('blood-bank.emergency.default_radius_km'),
                'max_radius_km' => config('blood-bank.emergency.max_radius_km'),
            ],
        ]);
    }

    public function store(StorePatientEmergencyRequestRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $hospital = Hospital::query()
            ->where('verification_status', 'approved')
            ->findOrFail($data['hospital_id']);

        try {
            $emergencyRequest = $this->broadcastService->broadcastForHospital(
                $request->user(),
                $hospital,
                $data,
            );
        } catch (BloodBankException $exception) {
            return back()->withInput()->with('error', $exception->getMessage());
        }

        if ($emergencyRequest->donors_notified_count === 0) {
            return back()->with('error', 'Your request was saved, but no eligible nearby donors could be alerted. Contact the selected blood bank directly.');
        }

        return back()->with('success', "Emergency request sent to {$emergencyRequest->donors_notified_count} nearby donor(s).");
    }

    public function registerDonor(StorePatientDonorProfileRequest $request): RedirectResponse
    {
        if (BloodDonor::query()->where('user_id', $request->user()->id)->exists()) {
            return back()->with('error', 'You already have a donor profile. Contact the blood bank to update your registered details.');
        }

        $donor = BloodDonor::query()->create([
            ...$request->safe()->except(['consent']),
            'user_id'      => $request->user()->id,
            'email'        => $request->user()->email,
            'is_verified'  => false,
            'is_available' => true,
            'consented_at' => now(),
        ]);

        return back()->with('success', 'Donor profile submitted. You can receive emergency alerts after staff verify your details.');
    }

    public function updateDonorProfile(UpdatePatientDonorProfileRequest $request): RedirectResponse
    {
        $donor = BloodDonor::query()->where('user_id', $request->user()->id)->firstOrFail();
        $data = $request->validated();
        $bloodGroupChanged = $donor->blood_group->value !== $data['blood_group'];

        $donor->update([
            ...$data,
            'is_verified' => $bloodGroupChanged ? false : $donor->is_verified,
            'verified_at' => $bloodGroupChanged ? null : $donor->verified_at,
        ]);

        return back()->with('success', $bloodGroupChanged
            ? 'Donor profile updated. Your changed blood group needs verification before you can receive alerts.'
            : 'Donor profile updated.');
    }

    public function updateDonorAvailability(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'is_available' => ['required', 'boolean'],
        ]);
        $donor = BloodDonor::query()->where('user_id', $request->user()->id)->firstOrFail();
        $donor->update(['is_available' => $data['is_available']]);

        return back()->with('success', $data['is_available']
            ? 'You are available for donor alerts.'
            : 'Donor alerts are paused.');
    }

    public function cancel(Request $request, EmergencyRequest $emergencyRequest): RedirectResponse
    {
        abort_unless($emergencyRequest->requested_by === $request->user()->id, 404);

        try {
            $this->broadcastService->close($emergencyRequest, EmergencyStatus::Cancelled);
        } catch (BloodBankException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('success', 'Emergency request cancelled.');
    }
}
