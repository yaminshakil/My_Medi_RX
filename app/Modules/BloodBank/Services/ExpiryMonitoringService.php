<?php

namespace App\Modules\BloodBank\Services;

use App\Models\User;
use App\Modules\BloodBank\Events\BloodStockUpdated;
use App\Modules\BloodBank\Notifications\NearExpiryNotification;
use App\Modules\BloodBank\Repositories\Contracts\BloodBagRepositoryInterface;
use App\Modules\BloodBank\Repositories\Contracts\EmergencyRequestRepositoryInterface;
use Illuminate\Support\Facades\Notification;

/** Daily sweep behind `php artisan blood-bank:check-expiry`. */
class ExpiryMonitoringService
{
    public function __construct(
        private readonly BloodBagRepositoryInterface $bags,
        private readonly EmergencyRequestRepositoryInterface $requests,
    ) {}

    /** @return array{expired:int, expiring_soon:int} */
    public function run(): array
    {
        // Expired units leave usable stock immediately; open emergency requests past their window close too.
        $expiredCount = $this->bags->markExpired();
        $this->requests->expireStale();

        $soon = $this->bags->expiringSoon(config('blood-bank.expiry_alert_days'), 500);

        if ($expiredCount > 0 || $soon->isNotEmpty()) {
            // One line per group + component keeps the digest short enough to read.
            $lines = $soon
                ->groupBy(fn ($bag) => $bag->blood_group->value.' '.$bag->component->label())
                ->map(fn ($group, $label) => sprintf(
                    '%s: %d unit(s), earliest %s',
                    $label,
                    $group->count(),
                    $group->min('expires_at')->format('d M H:i')
                ))
                ->values()
                ->all();

            // Everyone attached to a hospital gets the digest. Narrow this query if you add roles.
            Notification::send(
                User::query()->whereNotNull('hospital_id')->get(),
                new NearExpiryNotification($lines, $expiredCount)
            );
        }

        BloodStockUpdated::fire();

        return ['expired' => $expiredCount, 'expiring_soon' => $soon->count()];
    }
}
