<?php

namespace App\Modules\BloodBank\Console;

use App\Modules\BloodBank\Services\ExpiryMonitoringService;
use Illuminate\Console\Command;

class CheckBloodExpiry extends Command
{
    protected $signature = 'blood-bank:check-expiry';

    protected $description = 'Flag expired blood units and alert staff about units nearing expiry';

    public function handle(ExpiryMonitoringService $service): int
    {
        $result = $service->run();

        $this->info("Expired: {$result['expired']} | Expiring soon: {$result['expiring_soon']}");

        return self::SUCCESS;
    }
}
