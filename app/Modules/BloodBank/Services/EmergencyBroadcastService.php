<?php

namespace App\Modules\BloodBank\Services;

use App\Models\Hospital;
use App\Models\User;
use App\Modules\BloodBank\Enums\BloodComponent;
use App\Modules\BloodBank\Enums\BloodGroup;
use App\Modules\BloodBank\Enums\EmergencyStatus;
use App\Modules\BloodBank\Exceptions\BloodBankException;
use App\Modules\BloodBank\Models\BloodDonor;
use App\Modules\BloodBank\Models\EmergencyRequest;
use App\Modules\BloodBank\Notifications\EmergencyBloodRequestNotification;
use App\Modules\BloodBank\Repositories\Contracts\DonorRepositoryInterface;
use App\Modules\BloodBank\Repositories\Contracts\EmergencyRequestRepositoryInterface;
use App\Modules\BloodBank\Repositories\Contracts\HospitalRepositoryInterface;
use App\Modules\BloodBank\Support\BloodCompatibility;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/** One-click emergency request: find compatible verified donors near the hospital and alert them. */
class EmergencyBroadcastService
{
    public function __construct(
        private readonly EmergencyRequestRepositoryInterface $requests,
        private readonly DonorRepositoryInterface $donors,
        private readonly HospitalRepositoryInterface $hospitals,
    ) {}

    public function broadcast(User $user, array $data): EmergencyRequest
    {
        $hospital_id = DB::table('hospitals')
            ->where('created_by', $user->id)
            ->value('id');
        $hospital = Hospital::find($hospital_id);

        if (! $hospital) {
            throw new BloodBankException('Your account is not linked to a hospital, so there is no location to search from.');
        }

        if (is_null($hospital->latitude) || is_null($hospital->longitude)) {
            throw new BloodBankException("Add latitude and longitude to {$hospital->name} before broadcasting requests.");
        }

        $group = BloodGroup::from($data['blood_group']);
        $component = BloodComponent::from($data['component']);
        $radius = min((float) ($data['radius_km'] ?? config('blood-bank.emergency.default_radius_km')), (float) config('blood-bank.emergency.max_radius_km'));

        // Donors whose blood (or plasma/platelets) is compatible with the patient's group.
        $eligibleGroups = BloodCompatibility::donorGroupsFor($group, $component);

        [$request, $donors] = DB::transaction(function () use ($user, $hospital, $data, $group, $component, $radius, $eligibleGroups) {
            $request = $this->requests->create([
                'reference'    => 'EMR-'.now()->format('ymd').'-'.Str::upper(Str::random(5)),
                'hospital_id'  => $hospital->id,
                'requested_by' => $user->id,
                'blood_group'  => $group->value,
                'component'    => $component->value,
                'units_needed' => $data['units_needed'],
                'urgency'      => $data['urgency'],
                'radius_km'    => $radius,
                'note'         => $data['note'] ?? null,
                'status'       => EmergencyStatus::Open->value,
                'broadcast_at' => now(),
                'expires_at'   => now()->addHours(config('blood-bank.emergency.request_ttl_hours')),
            ]);

            $donors = $this->donors->findEligibleNearby(
                $hospital->latitude,
                $hospital->longitude,
                $radius,
                $eligibleGroups,
                config('blood-bank.emergency.max_donors_per_broadcast')
            );

            $this->requests->attachDonors(
                $request,
                $donors->mapWithKeys(fn (BloodDonor $d) => [$d->id => [
                    'distance_km' => round((float) $d->distance_km, 2),
                    'channel'     => 'mail',
                    'notified_at' => now(),
                ]])->all()
            );

            $this->requests->update($request, ['donors_notified_count' => $donors->count()]);

            return [$request, $donors];
        });

        // Send only after the transaction commits, so a rollback can never leave donors alerted about a request that does not exist.
        foreach ($donors as $donor) {
            $donor->notify(new EmergencyBloodRequestNotification($request, $donor, (float) $donor->distance_km));
        }

        return $request;
    }

    /** Donor tapped accept / decline in the email. False when the request is closed or the donor was never invited. */
    public function recordDonorResponse(EmergencyRequest $request, BloodDonor $donor, string $response): bool
    {
        if (! $request->isOpen()) {
            return false;
        }

        return $this->requests->recordResponse($request, $donor->id, $response);
    }

    public function close(EmergencyRequest $request, EmergencyStatus $status): EmergencyRequest
    {
        if (! in_array($request->status, [EmergencyStatus::Open], true)) {
            throw new BloodBankException('This request is already closed.');
        }

        return $this->requests->update($request, ['status' => $status->value, 'closed_at' => now()]);
    }
}
