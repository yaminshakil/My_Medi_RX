<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SubscriptionPaymentResource;
use App\Http\Resources\SubscriptionResource;
use App\Models\Subscription;
use App\Models\SubscriptionPayment;
use App\Models\SubscriptionPlan;
use App\Services\SslCommerzService;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SubscriptionController extends Controller
{
    public function __construct(private readonly SslCommerzService $gateway, private readonly SubscriptionService $subscriptions)
    {
    }

    public function current(Request $request)
    {
        $subscription = $request->user()->subscriptions()
            ->with('plan')
            ->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where('ends_at', '>', now())
            ->latest('ends_at')
            ->first();

        return response()->json([
            'success' => true,
            'data' => $subscription ? new SubscriptionResource($subscription) : null,
            'message' => $subscription ? 'Current subscription retrieved.' : 'No active subscription found.',
        ]);
    }

    public function history(Request $request)
    {
        $items = $request->user()->subscriptions()->with('plan')->latest()->paginate(15);
        return SubscriptionResource::collection($items)->additional(['success' => true]);
    }

    public function features(Request $request)
    {
        $subscription = $request->user()->subscriptions()->active()->with('plan')->latest('ends_at')->first();
        return response()->json([
            'success' => true,
            'data' => [
                'subscription_id' => $subscription?->id,
                'features' => $subscription?->plan?->features ?? [],
            ],
        ]);
    }

    public function activateFree(Request $request)
    {
        $validated = $request->validate(['plan_id' => ['required', 'integer', 'exists:subscription_plans,id']]);
        $user = $request->user();
        $role = $user->getRoleNames()->first() ?? 'Patient';
        $plan = SubscriptionPlan::query()->whereKey($validated['plan_id'])->where('is_active', true)->firstOrFail();
        abort_unless(in_array($plan->user_type, ['All', $role], true), 403);
        abort_unless((float) $plan->price === 0.0, 422, 'Only free plans can be activated without payment.');
        $subscription = $this->subscriptions->activateFreePlan($user, $plan);
        return response()->json(['success' => true, 'message' => 'Free subscription activated.', 'data' => new SubscriptionResource($subscription->load('plan'))], 201);
    }

    public function checkout(Request $request)
    {
        $validated = $request->validate(['plan_id' => ['required', 'integer', 'exists:subscription_plans,id']]);
        $user = $request->user();
        $role = $user->getRoleNames()->first() ?? 'Patient';
        $plan = SubscriptionPlan::query()->whereKey($validated['plan_id'])->where('is_active', true)->firstOrFail();
        abort_unless(in_array($plan->user_type, ['All', $role], true), 403, 'This plan is not available for your account type.');

        if ((float) $plan->price <= 0) {
            throw ValidationException::withMessages(['plan_id' => 'Free plans do not require SSLCommerz checkout. Contact support or use the free-plan activation endpoint.']);
        }

        return $this->startCheckout($request, $plan);
    }

    public function renew(Request $request, Subscription $subscription)
    {
        abort_unless((int) $subscription->user_id === (int) $request->user()->id, 404);
        $subscription->load('plan');
        abort_unless($subscription->plan && $subscription->plan->is_active, 422, 'The subscription plan is no longer available.');
        return $this->startCheckout($request, $subscription->plan);
    }

    private function startCheckout(Request $request, SubscriptionPlan $plan)
    {
        $user = $request->user();
        [$subscription, $payment] = DB::transaction(function () use ($user, $plan) {
            $subscription = Subscription::create([
                'user_id' => $user->id,
                'subscription_plan_id' => $plan->id,
                'status' => 'pending',
                'auto_renew' => false,
            ]);

            $payment = SubscriptionPayment::create([
                'subscription_id' => $subscription->id,
                'user_id' => $user->id,
                'amount' => $plan->price,
                'currency' => config('sslcommerz.currency', 'BDT'),
                'transaction_id' => 'MDX' . Str::upper(Str::random(24)),
                'status' => 'pending',
            ]);
            return [$subscription, $payment];
        });

        try {
            $frontend = rtrim(config('app.url'), '/');
            $response = $this->gateway->createSession([
                'total_amount' => number_format((float) $payment->amount, 2, '.', ''),
                'currency' => $payment->currency,
                'tran_id' => $payment->transaction_id,
                'success_url' => route('api.v1.api.sslcommerz.success'),
                'fail_url' => route('api.v1.api.sslcommerz.fail'),
                'cancel_url' => route('api.v1.api.sslcommerz.cancel'),
                'ipn_url' => route('api.v1.api.sslcommerz.ipn'),
                'cus_name' => $user->name,
                'cus_email' => $user->email,
                'cus_phone' => $user->phone ?: '01700000000',
                'cus_add1' => $user->address ?: 'Bangladesh',
                'cus_city' => 'Dhaka',
                'cus_country' => 'Bangladesh',
                'shipping_method' => 'NO',
                'product_name' => $plan->name,
                'product_category' => 'Subscription',
                'product_profile' => 'general',
                'num_of_item' => 1,
            ]);

            $payment->update(['session_key' => $response['sessionkey'] ?? null]);

            return response()->json([
                'success' => true,
                'message' => 'Checkout session created.',
                'data' => [
                    'subscription_id' => $subscription->id,
                    'payment_id' => $payment->id,
                    'transaction_id' => $payment->transaction_id,
                    'gateway_url' => $response['GatewayPageURL'],
                ],
            ], 201);
        } catch (\Throwable $e) {
            Log::error('SSLCommerz checkout initialization failed', [
                'user_id' => $user->id,
                'subscription_id' => $subscription->id,
                'payment_id' => $payment->id,
                'transaction_id' => $payment->transaction_id,
                'error' => $e->getMessage(),
            ]);

            $payment->update(['status' => 'failed', 'gateway_response' => ['error' => $e->getMessage()]]);
            $subscription->update(['status' => 'cancelled']);
            return response()->json([
                'success' => false,
                'message' => 'Unable to initialize SSLCommerz checkout.',
                'errors' => ['payment' => ['Please check gateway configuration and try again.']],
            ], 502);
        }
    }

    public function cancel(Request $request, Subscription $subscription)
    {
        abort_unless((int) $subscription->user_id === (int) $request->user()->id, 404);
        if ($subscription->status !== 'active') {
            return response()->json(['success' => false, 'message' => 'Only active subscriptions can be cancelled.'], 422);
        }
        // Manual billing has no automatic charge to cancel. Keep paid access active;
        // the customer simply chooses not to renew when the term expires.
        $subscription->update(['cancel_at_period_end' => true, 'auto_renew' => false]);
        return response()->json(['success' => true, 'message' => 'Automatic renewal is disabled. Your current paid access remains active until expiry.']);
    }

    public function payments(Request $request)
    {
        $payments = $request->user()->subscriptionPayments()->with('subscription.plan')->latest()->paginate(15);
        return SubscriptionPaymentResource::collection($payments)->additional(['success' => true]);
    }

    public function payment(Request $request, SubscriptionPayment $payment)
    {
        abort_unless((int) $payment->user_id === (int) $request->user()->id, 404);
        return (new SubscriptionPaymentResource($payment->load('subscription.plan')))->additional(['success' => true]);
    }
}
