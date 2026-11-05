<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\SubscriptionPaymentResource;
use App\Http\Resources\SubscriptionResource;
use App\Models\Subscription;
use App\Models\SubscriptionPayment;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;

class SubscriptionManagementController extends Controller
{
    public function __construct(private readonly SubscriptionService $subscriptions) {}
    public function subscriptions(Request $request)
    {
        $query = Subscription::with(['user:id,name,email', 'plan'])->latest();
        if ($request->filled('status')) $query->where('status', $request->string('status'));
        if ($request->filled('user_type')) $query->whereHas('plan', fn ($q) => $q->where('user_type', $request->string('user_type')));
        return SubscriptionResource::collection($query->paginate(25))->additional(['success' => true]);
    }

    public function payments(Request $request)
    {
        $query = SubscriptionPayment::with(['subscription.plan', 'user:id,name,email'])->latest();
        if ($request->filled('status')) $query->where('status', $request->string('status'));
        return SubscriptionPaymentResource::collection($query->paginate(25))->additional(['success' => true]);
    }

    public function approvePayment(SubscriptionPayment $payment)
    {
        abort_unless($payment->status === 'review' && is_array($payment->gateway_response), 422, 'This payment is not awaiting review.');
        $validation = $payment->gateway_response;
        $validation['risk_level'] = 0; // Explicit Admin approval after reviewing the validated gateway response.
        $this->subscriptions->markPaid($payment, $validation);
        return response()->json(['success' => true, 'message' => 'Payment approved and subscription activated.']);
    }

    public function reviewPayment(SubscriptionPayment $payment)
    {
        abort_unless($payment->status === 'review', 422, 'This payment is not awaiting review.');
        return response()->json(['success' => true, 'data' => [
            'id' => $payment->id,
            'transaction_id' => $payment->transaction_id,
            'amount' => (float) $payment->amount,
            'currency' => $payment->currency,
            'gateway_response' => $payment->gateway_response,
        ]]);
    }
}
