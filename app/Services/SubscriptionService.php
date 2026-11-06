<?php

namespace App\Services;

use App\Models\Subscription;
use App\Models\SubscriptionPayment;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SubscriptionService
{
    public function markPaid(SubscriptionPayment $payment, array $validated): void
    {
        DB::transaction(function () use ($payment, $validated) {
            $locked = SubscriptionPayment::query()->lockForUpdate()->findOrFail($payment->id);
            if ($locked->status === 'paid') {
                return;
            }
            if (! in_array(($validated['status'] ?? null), ['VALID', 'VALIDATED'], true)) {
                throw ValidationException::withMessages(['payment' => 'Payment is not valid.']);
            }
            if (($validated['tran_id'] ?? null) !== $locked->transaction_id) {
                throw ValidationException::withMessages(['payment' => 'Transaction reference mismatch.']);
            }
            if (number_format((float) ($validated['amount'] ?? 0), 2, '.', '') !== number_format((float) $locked->amount, 2, '.', '')) {
                throw ValidationException::withMessages(['payment' => 'Payment amount mismatch.']);
            }
            if (strtoupper($validated['currency'] ?? '') !== strtoupper($locked->currency)) {
                throw ValidationException::withMessages(['payment' => 'Payment currency mismatch.']);
            }
            if ((int) ($validated['risk_level'] ?? 0) === 1) {
                $locked->update(['status' => 'review', 'validation_id' => $validated['val_id'] ?? null, 'gateway_response' => $validated]);

                return; // Admin must review risky transactions before activation.
            }
            $sub = Subscription::query()->lockForUpdate()->findOrFail($locked->subscription_id);
            $now = now();
            $base = $sub->ends_at && $sub->ends_at->isFuture() ? $sub->ends_at->copy() : $now;
            $days = (int) $sub->plan()->value('duration_days');
            $sub->update(['starts_at' => now(), 'ends_at' => $base->addDays($days), 'status' => 'active']);
            $locked->update(['status' => 'paid', 'paid_at' => now(), 'validation_id' => $validated['val_id'] ?? null,
                'bank_transaction_id' => $validated['bank_tran_id'] ?? null, 'payment_method' => $validated['card_type'] ?? null, 'gateway_response' => $validated]);
        });
    }

    public function hasActiveSubscription($user): bool
    {
        return $user->subscriptions()->active()->exists();
    }

    public function canUseFeature($user, string $feature): bool
    {
        $sub = $user->subscriptions()->active()->with('plan')->latest('ends_at')->first();

        return $sub && (bool) data_get($sub->plan->features, $feature, false);
    }
}
