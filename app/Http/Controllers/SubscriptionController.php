<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use App\Models\SubscriptionPayment;
use App\Models\SubscriptionPlan;
use App\Services\SslCommerzService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

class SubscriptionController extends Controller
{
    public function __construct(private SslCommerzService $gateway) {}

    public function index(Request $request)
    {
        $role = $request->user()->getRoleNames()->first() ?? 'Patient';
        $plans = SubscriptionPlan::where('is_active', true)->whereIn('user_type', ['All', $role])->orderBy('price')->get();
        $current = $request->user()->subscriptions()->with('plan')->where('status', 'active')->where('ends_at', '>', now())->latest('ends_at')->first();

        return Inertia::render('subscriptions/Index', ['plans' => $plans, 'current' => $current]);
    }

    public function checkout(Request $request, SubscriptionPlan $plan): Response
    {
        $user = $request->user();
        $role = $user->getRoleNames()->first() ?? 'Patient';
        abort_unless($plan->is_active && in_array($plan->user_type, ['All', $role], true), 404);
        $subscription = Subscription::create(['user_id' => $user->id, 'subscription_plan_id' => $plan->id, 'status' => 'pending']);
        $payment = SubscriptionPayment::create(['subscription_id' => $subscription->id, 'user_id' => $user->id, 'amount' => $plan->price, 'currency' => config('sslcommerz.currency'), 'transaction_id' => 'MDX'.Str::upper(Str::random(24)), 'status' => 'pending']);
        try {
            $response = $this->gateway->createSession([
                'total_amount'     => $payment->amount,
                'currency'         => $payment->currency,
                'tran_id'          => $payment->transaction_id,
                'success_url'      => route('sslcommerz.success'),
                'fail_url'         => route('sslcommerz.fail'),
                'cancel_url'       => route('sslcommerz.cancel'),
                'ipn_url'          => route('sslcommerz.ipn'),
                'cus_name'         => $user->name,
                'cus_email'        => $user->email,
                'cus_phone'        => $user->phone ?? '01700000000',
                'cus_add1'         => $user->address ?? 'Bangladesh',
                'cus_city'         => 'Dhaka',
                'cus_country'      => 'Bangladesh',
                'shipping_method'  => 'NO',
                'product_name'     => $plan->name,
                'product_category' => 'Subscription',
                'product_profile'  => 'general',
                'num_of_item'      => 1,
            ]);
            $payment->update(['session_key' => $response['sessionkey'] ?? null]);

            return Inertia::location($response['GatewayPageURL']);
        } catch (\Throwable $e) {
            Log::error('SSLCommerz checkout initialization failed', [
                'user_id'         => $user->id,
                'subscription_id' => $subscription->id,
                'payment_id'      => $payment->id,
                'transaction_id'  => $payment->transaction_id,
                'error'           => $e->getMessage(),
            ]);
            $payment->update(['status' => 'failed']);
            $subscription->update(['status' => 'cancelled']);
            report($e);

            return back()->withErrors(['payment' => 'Unable to start SSLCommerz checkout. Please try again.']);
        }
    }

    public function history(Request $request)
    {
        return Inertia::render('subscriptions/History', ['payments' => $request->user()->subscriptionPayments()->with('subscription.plan')->latest()->paginate(15)]);
    }

    public function result()
    {
        return Inertia::render('subscriptions/Result', ['message' => session('payment_message', 'Your payment status is being checked.')]);
    }
}
