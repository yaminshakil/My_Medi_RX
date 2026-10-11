<?php

namespace App\Modules\BloodBank\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * Fired whenever stock changes (unit registered, issued, discarded, expired).
 * Open dashboards listen on the private channel and reload the stock props.
 */
class BloodStockUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets;

    /** Dispatch only when the feature flag is on, so installs without websockets stay quiet. */
    public static function fire(): void
    {
        if (config('blood-bank.broadcast_stock_updates')) {
            static::dispatch();
        }
    }

    public function broadcastOn(): array
    {
        return [new PrivateChannel('blood-bank.stock')];
    }

    public function broadcastAs(): string
    {
        return 'stock.updated';
    }

    public function broadcastWith(): array
    {
        return ['at' => now()->toIso8601String()];
    }
}
