<?php

namespace App\Services\Sms\Drivers;

use App\Services\Sms\SmsInterface;
use Illuminate\Support\Facades\Log;

class SandboxSmsDriver implements SmsInterface
{
    public function send(string $to, string $message): bool
    {
        Log::info("[SANDBOX SMS] To: {$to} | Message: {$message}");
        return true;
    }
}
