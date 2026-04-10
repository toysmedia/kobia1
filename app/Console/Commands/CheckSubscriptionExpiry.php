<?php
namespace App\Console\Commands;
use App\Models\System\Tenant;
use App\Services\SubscriptionService;
use Illuminate\Console\Command;
class CheckSubscriptionExpiry extends Command
{
    protected $signature = 'subscriptions:check-expiry';
    protected $description = 'Check tenant subscription expiries and send warnings';

    public function __construct(private SubscriptionService $subscriptionService)
    {
        parent::__construct();
    }

    public function handle(): void
    {
        $expiringSoon = Tenant::where('status', 'active')
            ->whereDate('subscription_expires_at', now()->addDays(7)->toDateString())->get();
        foreach ($expiringSoon as $tenant) {
            $this->info("7-day warning for tenant: {$tenant->name}");
        }
        $expiringUrgent = Tenant::where('status', 'active')
            ->whereDate('subscription_expires_at', now()->addDays(3)->toDateString())->get();
        foreach ($expiringUrgent as $tenant) {
            $this->info("3-day urgent warning for tenant: {$tenant->name}");
        }
        $expired = Tenant::where('status', 'active')
            ->where('subscription_expires_at', '<', now())->get();
        foreach ($expired as $tenant) {
            $this->subscriptionService->expire($tenant);
            $this->info("Marked expired: {$tenant->name}");
        }
        $this->info('Subscription expiry check complete.');
    }
}
