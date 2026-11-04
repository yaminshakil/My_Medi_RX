<?php
namespace App\Console\Commands;
use App\Models\Subscription;
use Illuminate\Console\Command;
class ExpireSubscriptions extends Command {
    protected $signature = 'subscriptions:expire';
    protected $description = 'Expire subscriptions whose paid period has ended';
    public function handle(): int {
        $count = Subscription::where('status','active')->whereNotNull('ends_at')->where('ends_at','<=',now())->update(['status'=>'expired']);
        $this->info("Expired {$count} subscriptions."); return self::SUCCESS;
    }
}
