<?php

namespace App\Console\Commands;

use App\Services\TenantService;
use Illuminate\Console\Command;

/**
 * Artisan command to provision a new ISP tenant.
 *
 * Usage:
 *   php artisan tenant:create "Kenya ISP" admin@kenyaisp.co.ke kenya-isp
 *   php artisan tenant:create "Kenya ISP" admin@kenyaisp.co.ke kenya-isp --plan=professional
 */
class CreateTenantCommand extends Command
{
    protected $signature = 'tenant:create
                            {name        : The display name of the ISP}
                            {email       : Admin email address for this tenant}
                            {subdomain   : Unique subdomain slug (e.g. kenya-isp)}
                            {--plan=     : Pricing plan slug (defaults to first active plan)}';

    protected $description = 'Provision a new ISP tenant: creates the system record, database, migrations, and default seed data.';

    public function handle(TenantService $tenantService): int
    {
        $subdomain = $this->argument('subdomain');

        $this->info("Provisioning tenant: {$subdomain}");

        try {
            $result = $tenantService->create([
                'name'      => $this->argument('name'),
                'email'     => $this->argument('email'),
                'subdomain' => $subdomain,
                'plan'      => $this->option('plan') ?: null,
            ]);

            $tenant = $result['tenant'];

            $this->info('✅ Tenant provisioned successfully!');
            $this->table(
                ['Field', 'Value'],
                [
                    ['Tenant ID', $tenant->id],
                    ['Name', $tenant->name],
                    ['Email', $tenant->email],
                    ['Subdomain', $tenant->subdomain],
                    ['Database', $tenant->database_name],
                    ['DB User', $tenant->database_username],
                    ['Status', $tenant->status],
                    ['Trial Ends', $tenant->trial_ends_at?->toDateString() ?? 'N/A'],
                ],
            );
        } catch (\Throwable $e) {
            $this->error("Failed to provision tenant: {$e->getMessage()}");

            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
