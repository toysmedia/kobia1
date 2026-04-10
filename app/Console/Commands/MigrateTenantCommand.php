<?php

namespace App\Console\Commands;

use App\Models\System\Tenant;
use App\Services\DatabaseManager;
use Illuminate\Console\Command;

/**
 * Artisan command to run tenant migrations.
 *
 * Usage:
 *   php artisan tenant:migrate kenya-isp
 *   php artisan tenant:migrate --all
 *   php artisan tenant:migrate kenya-isp --seed
 *   php artisan tenant:migrate kenya-isp --fresh
 *   php artisan tenant:migrate --all --fresh --seed
 */
class MigrateTenantCommand extends Command
{
    protected $signature = 'tenant:migrate
                            {subdomain?  : The subdomain slug of a specific tenant}
                            {--all       : Run migrations for ALL active tenants}
                            {--seed      : Seed the tenant database after migration}
                            {--fresh     : Drop all tables and re-run all migrations}';

    protected $description = 'Run tenant database migrations for one or all tenants.';

    public function handle(DatabaseManager $dbManager): int
    {
        $subdomain = $this->argument('subdomain');
        $all       = (bool) $this->option('all');
        $seed      = (bool) $this->option('seed');
        $fresh     = (bool) $this->option('fresh');

        if (! $subdomain && ! $all) {
            $this->error('Please specify a subdomain or use the --all flag.');

            return self::FAILURE;
        }

        $tenants = $all
            ? Tenant::all()
            : Tenant::where('subdomain', $subdomain)->get();

        if ($tenants->isEmpty()) {
            $this->error("No tenant found for subdomain: {$subdomain}");

            return self::FAILURE;
        }

        foreach ($tenants as $tenant) {
            $this->info("Running migrations for tenant: {$tenant->subdomain}");

            try {
                $dbManager->runTenantMigrations($tenant, seed: $seed, fresh: $fresh);
                $this->info("✅ Done: {$tenant->subdomain}");
            } catch (\Throwable $e) {
                $this->error("Failed for {$tenant->subdomain}: {$e->getMessage()}");
            }
        }

        return self::SUCCESS;
    }
}
