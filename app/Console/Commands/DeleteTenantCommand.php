<?php

namespace App\Console\Commands;

use App\Services\TenantService;
use Illuminate\Console\Command;

/**
 * Artisan command to delete a tenant and optionally drop its database.
 *
 * Usage:
 *   php artisan tenant:delete kenya-isp
 *   php artisan tenant:delete kenya-isp --force
 */
class DeleteTenantCommand extends Command
{
    protected $signature = 'tenant:delete
                            {subdomain   : The subdomain slug of the tenant to delete}
                            {--force     : Also drop the MySQL database and database user}';

    protected $description = 'Delete a tenant. Use --force to also drop the MySQL database and user.';

    public function handle(TenantService $tenantService): int
    {
        $subdomain = $this->argument('subdomain');
        $force     = (bool) $this->option('force');

        $warning = $force
            ? "This will PERMANENTLY delete tenant '{$subdomain}' AND drop its MySQL database. This cannot be undone!"
            : "This will soft-delete tenant '{$subdomain}'. The database will be preserved.";

        $this->warn($warning);

        if (! $this->confirm('Are you sure you want to proceed?')) {
            $this->info('Aborted.');

            return self::SUCCESS;
        }

        try {
            $tenantService->delete($subdomain, $force);
            $this->info("✅ Tenant '{$subdomain}' deleted successfully.");
        } catch (\Throwable $e) {
            $this->error("Failed to delete tenant: {$e->getMessage()}");

            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
