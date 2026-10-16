<?php

namespace App\Modules\BloodBank\Services;

use App\Models\User;
use App\Modules\BloodBank\Enums\BagStatus;
use App\Modules\BloodBank\Enums\BloodComponent;
use App\Modules\BloodBank\Events\BloodStockUpdated;
use App\Modules\BloodBank\Exceptions\BloodBankException;
use App\Modules\BloodBank\Models\BloodBag;
use App\Modules\BloodBank\Repositories\Contracts\BloodBagRepositoryInterface;
use Illuminate\Support\Carbon;

/** Write-side logic for blood units: register, release from quarantine, discard. */
class BloodBagService
{
    public function __construct(private readonly BloodBagRepositoryInterface $bags) {}

    public function register(User $user, array $data): BloodBag
    {
        $component = BloodComponent::from($data['component']);
        $collected = Carbon::parse($data['collected_at']);

        // No expiry typed in? Fall back to the standard shelf life for the component.
        $expires = ! empty($data['expires_at'])
            ? Carbon::parse($data['expires_at'])
            : $collected->copy()->addDays($component->shelfLifeDays());

        $bag = $this->bags->create([
            'bag_number'      => $data['bag_number'],
            'batch_number'    => $data['batch_number'],
            'donor_id'        => $data['donor_id'] ?? null,
            'blood_group'     => $data['blood_group'],
            'component'       => $component->value,
            'volume_ml'       => $data['volume_ml'],
            'collected_at'    => $collected,
            'expires_at'      => $expires,
            'storage_unit_id' => $data['storage_unit_id'],
            // Quarantined units do not count as stock until screening clears them.
            'status'        => ! empty($data['quarantine']) ? BagStatus::Quarantined->value : BagStatus::Available->value,
            'registered_by' => $user->id,
        ]);

        BloodStockUpdated::fire();

        return $bag;
    }

    public function release(BloodBag $bag): BloodBag
    {
        if ($bag->status !== BagStatus::Quarantined) {
            throw new BloodBankException('Only quarantined units can be released.');
        }

        $this->bags->update($bag, ['status' => BagStatus::Available->value]);
        BloodStockUpdated::fire();

        return $bag;
    }

    public function discard(BloodBag $bag, string $reason): BloodBag
    {
        // Issued units are already in a patient's record; they cannot be discarded from stock.
        if (in_array($bag->status, [BagStatus::Issued, BagStatus::Discarded], true)) {
            throw new BloodBankException("A {$bag->status->value} unit cannot be discarded.");
        }

        $this->bags->update($bag, [
            'status'         => BagStatus::Discarded->value,
            'discard_reason' => $reason,
            'discarded_at'   => now(),
        ]);

        BloodStockUpdated::fire();

        return $bag;
    }
}
