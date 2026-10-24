<?php

namespace App\Services\Sms;

use App\Services\Sms\Drivers\BulkSmsBdDriver;
use App\Services\Sms\Drivers\GreenwebSmsDriver;
use App\Services\Sms\Drivers\SandboxSmsDriver;
use InvalidArgumentException;

class SmsManager implements SmsInterface
{
    protected SmsInterface $driver;

    public function __construct()
    {
        $this->driver = $this->resolve(config('sms.default'));
    }

    protected function resolve(string $driver): SmsInterface
    {
        return match ($driver) {
            'sandbox'   => new SandboxSmsDriver,
            'greenweb'  => new GreenwebSmsDriver,
            'bulksmsbd' => new BulkSmsBdDriver,
            default     => throw new InvalidArgumentException("Unsupported SMS driver [{$driver}]."),
        };
    }

    public function send(string $to, string $message): bool
    {
        return $this->driver->send($to, $message);
    }
}
