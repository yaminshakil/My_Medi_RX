<?php

namespace App\Http\Controllers;

use App\Models\SubscriptionPayment;
use App\Services\SslCommerzService;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class SslCommerzController extends Controller
{
    public function __construct(private SslCommerzService $gateway, private SubscriptionService $subscriptions)
    {
    }
    public function success(Request $request)
    {
        $this->process($request);
        return redirect()->route('subscriptions.result')->with('payment_message', 'Payment verification is processing.');
    }
    public function fail(Request $request)
    {
        $this->markFailure($request);
        return redirect()->route('subscriptions.result')->with('payment_message', 'Payment failed. You can try again.');
    }
    public function cancel(Request $request)
    {
        $this->markFailure($request, 'cancelled');
        return redirect()->route('subscriptions.result')->with('payment_message', 'Payment was cancelled.');
    }
    public function ipn(Request $request)
    {
        $this->process($request);
        return response('OK', 200);
    }
    private function process(Request $request): void
    {
        $tranId = (string)$request->input('tran_id');
        $valId = (string)$request->input('val_id');
        if (!$tranId || !$valId) {
            Log::warning('SSLCommerz callback missing identifiers');
            return;
        }
        $payment = SubscriptionPayment::where('transaction_id', $tranId)->first();
        if (!$payment || $payment->status === 'paid') {
            return;
        }
        try {
            $validated = $this->gateway->validate($valId);
            $this->subscriptions->markPaid($payment, $validated);
        } catch (\Throwable $e) {
            Log::error('SSLCommerz payment verification failed', ['tran_id' => $tranId,'error' => $e->getMessage()]);
        }
    }
    private function markFailure(Request $request, string $status = 'failed'): void
    {
        $payment = SubscriptionPayment::where('transaction_id', $request->input('tran_id'))->first();
        if ($payment && $payment->status === 'pending') {
            $payment->update(['status' => $status,'gateway_response' => $request->except(['store_passwd'])]);
        }
    }
}
