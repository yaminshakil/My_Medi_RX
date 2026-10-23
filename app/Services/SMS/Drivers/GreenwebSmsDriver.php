<?php

namespace App\Services\Sms\Drivers;

use App\Services\Sms\SmsInterface;
use Illuminate\Support\Facades\Http;

class GreenwebSmsDriver implements SmsInterface
{
    public function send(string $to, string $message): bool
    {
        $response = Http::asForm()->post(config('sms.drivers.greenweb.url'), [
            'token' => config('sms.drivers.greenweb.token'),
            'to' => $to,
            'message' => $message,
        ]);

        // Greenweb usually returns JSON or status string
        return $response->successful();
    }
}
