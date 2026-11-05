<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPayment;
use App\Services\SslCommerzService;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SslCommerzCallbackController extends Controller
{
    public function __construct(
        private readonly SslCommerzService $gateway,
        private readonly SubscriptionService $subscriptions,
    ) {}

    public function success(Request $request)
    {
        $result = $this->verifyAndProcess($request);
        return redirect($this->frontendResultUrl($request, $result));
    }

    public function fail(Request $request)
    {
        $this->markUnsuccessful($request, 'failed');
        return redirect($this->frontendResultUrl($request, 'failed'));
    }

    public function cancel(Request $request)
    {
        $this->markUnsuccessful($request, 'cancelled');
        return redirect($this->frontendResultUrl($request, 'cancelled'));
    }

    public function ipn(Request $request)
    {
        $result = $this->verifyAndProcess($request);
        return response()->json(['received' => true, 'result' => $result], 200);
    }

    private function verifyAndProcess(Request $request): string
    {
        $tranId = (string) $request->input('tran_id');
        $valId = (string) $request->input('val_id');
        if ($tranId === '' || $valId === '') {
            Log::warning('SSLCommerz callback missing identifiers', ['payload' => $request->except(['store_passwd'])]);
            return 'verification_failed';
        }

        $payment = SubscriptionPayment::where('transaction_id', $tranId)->first();
        if (!$payment) {
            Log::warning('SSLCommerz callback transaction not found', ['tran_id' => $tranId]);
            return 'verification_failed';
        }
        if ($payment->status === 'paid') return 'verified';

        try {
            $validation = $this->gateway->validate($valId);
            // Never trust callback fields for amount/status; use the validator API response.
            $this->subscriptions->markPaid($payment, $validation);
            return match ($payment->fresh()->status) {
                'paid' => 'verified',
                'review' => 'review',
                default => 'verification_failed',
            };
        } catch (\Throwable $e) {
            Log::error('SSLCommerz payment verification failed', [
                'tran_id' => $tranId,
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);
            return 'verification_failed';
        }
    }

    private function markUnsuccessful(Request $request, string $status): void
    {
        $tranId = (string) $request->input('tran_id');
        if ($tranId === '') return;

        $payment = SubscriptionPayment::where('transaction_id', $tranId)->first();
        if (!$payment || $payment->status !== 'pending') return;

        $payment->update([
            'status' => $status,
            'gateway_response' => $request->except(['store_passwd']),
        ]);
        $payment->subscription()->where('status', 'pending')->update(['status' => $status]);
    }

    private function frontendResultUrl(Request $request, string $status): string
    {
        $base = rtrim(config('subscriptions.frontend_url', config('app.url')), '/');
        return $base . '/subscriptions/result?status=' . urlencode($status)
            . '&tran_id=' . urlencode((string) $request->input('tran_id'));
    }
}
