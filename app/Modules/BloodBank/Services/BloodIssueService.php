<?php

namespace App\Modules\BloodBank\Services;

use App\Models\User;
use App\Modules\BloodBank\Enums\BagStatus;
use App\Modules\BloodBank\Enums\BloodComponent;
use App\Modules\BloodBank\Enums\BloodGroup;
use App\Modules\BloodBank\Events\BloodStockUpdated;
use App\Modules\BloodBank\Exceptions\BloodBankException;
use App\Modules\BloodBank\Models\BloodIssue;
use App\Modules\BloodBank\Models\Patient;
use App\Modules\BloodBank\Repositories\Contracts\BloodBagRepositoryInterface;
use App\Modules\BloodBank\Repositories\Contracts\BloodIssueRepositoryInterface;
use App\Modules\BloodBank\Repositories\Contracts\PatientRepositoryInterface;
use App\Modules\BloodBank\Support\BloodCompatibility;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/** Dispenses units to patients and keeps the transfusion audit trail. */
class BloodIssueService
{
    public function __construct(
        private readonly BloodBagRepositoryInterface $bags,
        private readonly BloodIssueRepositoryInterface $issues,
        private readonly PatientRepositoryInterface $patients,
    ) {}

    /**
     * Units the issue form may offer, soonest-expiring first, compatible ones before the rest.
     * Without $includeAll only compatible groups are queried; an untyped patient shows everything flagged incompatible.
     */
    public function availableBagsFor(?Patient $patient, BloodComponent $component, bool $includeAll = false): array
    {
        $recipient = $patient?->blood_group;

        $groups = ($recipient && ! $includeAll)
            ? BloodCompatibility::donorGroupsFor($recipient, $component)
            : BloodGroup::cases();

        return $this->bags->availableFor($groups, $component, 40)
            ->map(fn ($bag) => [
                'id'           => $bag->id,
                'bag_number'   => $bag->bag_number,
                'batch_number' => $bag->batch_number,
                'blood_group'  => $bag->blood_group->value,
                'expires_at'   => $bag->expires_at->toIso8601String(),
                'hours_left'   => $bag->hoursLeft(),
                'compatible'   => $recipient ? BloodCompatibility::isCompatible($bag->blood_group, $recipient, $component) : false,
            ])
            ->sortByDesc('compatible') // stable sort keeps the first-expiry order inside each half
            ->values()
            ->all();
    }

    public function issue(User $user, array $data): BloodIssue
    {
        $issue = DB::transaction(function () use ($user, $data) {
            // Lock the bag row: if two staff submit the same unit, the second one finds it already issued.
            $bag = $this->bags->lockAvailable((int) $data['blood_bag_id']);

            if (! $bag) {
                throw new BloodBankException('That unit is no longer available. Pick another one.');
            }
            if ($bag->expires_at->isPast()) {
                throw new BloodBankException('That unit has expired and cannot be issued.');
            }

            $patient = $this->patients->findOrFail((int) $data['patient_id']);

            $compatible = $patient->blood_group
                && BloodCompatibility::isCompatible($bag->blood_group, $patient->blood_group, $bag->component);

            // An incompatible (or untyped) match is allowed only with a written reason, which is stored on the log entry.
            if (! $compatible && blank($data['compatibility_override_reason'] ?? null)) {
                throw new BloodBankException(
                    $patient->blood_group
                        ? "{$bag->blood_group->value} {$bag->component->label()} is not compatible with {$patient->blood_group->value}. Enter an override reason to continue."
                        : 'This patient has no blood group on file. Enter an override reason to continue.'
                );
            }

            $issue = $this->issues->create([
                'issue_number'                  => 'ISS-'.now()->format('ymd').'-'.Str::upper(Str::random(5)),
                'blood_bag_id'                  => $bag->id,
                'patient_id'                    => $patient->id,
                'physician_id'                  => $data['physician_id'],
                'hospital_id'                   => $user->hospital_id,
                'emergency_request_id'          => $data['emergency_request_id'] ?? null,
                'issued_by'                     => $user->id,
                'indication'                    => $data['indication'],
                'ward'                          => $data['ward'] ?? null,
                'compatibility_override_reason' => $compatible ? null : $data['compatibility_override_reason'],
                'issued_at'                     => now(),
            ]);

            $this->bags->update($bag, ['status' => BagStatus::Issued->value]);

            return $issue;
        });

        BloodStockUpdated::fire();

        return $issue;
    }

    /** Bedside follow-up: transfusion start/end times and any reaction. */
    public function recordOutcome(BloodIssue $issue, array $data): BloodIssue
    {
        return $this->issues->update($issue, [
            'transfusion_started_at'   => $data['transfusion_started_at'] ?? null,
            'transfusion_completed_at' => $data['transfusion_completed_at'] ?? null,
            'reaction_noted'           => (bool) ($data['reaction_noted'] ?? false),
            'reaction_notes'           => ! empty($data['reaction_noted']) ? ($data['reaction_notes'] ?? null) : null,
        ]);
    }
}
