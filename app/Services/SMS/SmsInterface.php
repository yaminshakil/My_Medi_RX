<?php

namespace App\Services\Sms;

interface SmsInterface
{
    public function send(string $to, string $message): bool;
}
