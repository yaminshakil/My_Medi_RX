<?php

namespace App\Services;

use App\Models\License;

class LicenseService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function isActive(string $licenseKey, ?string $domain = null): bool
    {
        $license = License::where('license_key', $licenseKey)->first();

        if (! $license) {
            return false;
        }

        if ($license->status !== 'active') {
            return false;
        }

        if ($license->expires_at && $license->expires_at->isPast()) {
            return false;
        }

        if ($domain && $license->domain !== $domain) {
            return false;
        }

        return true;
    }
}
