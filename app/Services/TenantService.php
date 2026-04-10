<?php

namespace App\Services;

use App\Models\System\PricingPlan;
use App\Models\System\Tenant;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * TenantService handles the full tenant provisioning lifecycle.
 *
 * Steps performed when creating a new tenant:
 *  1. Validate subdomain uniqueness
 *  2. Create the tenant record in the system DB
 *  3. Generate a unique MySQL database + user for the tenant
 *  4. Run tenant DB migrations
 *  5. Seed default data (admin user, roles, settings)
 *  6. Return the tenant credentials
 */
class TenantService
{
    public function __construct(
        private readonly DatabaseManager $databaseManager,
    ) {}

    // -------------------------------------------------------------------------
    // Provisioning
    // -------------------------------------------------------------------------

    /**
     * Provision a brand-new tenant.
     *
     * @param  array{name: string, email: string, phone?: string, subdomain: string, plan?: string}  $data
     * @return array{tenant: Tenant, admin_password: string}
     */
    public function create(array $data): array
    {
        $subdomain    = Str::slug($data['subdomain']);
        $dbName       = "oxnet_tenant_{$subdomain}";
        $dbUsername   = "oxnet_{$subdomain}";
        $dbPassword   = Str::random(32);

        // Resolve plan (default to first active plan)
        $plan = isset($data['plan'])
            ? PricingPlan::where('slug', $data['plan'])->firstOrFail()
            : PricingPlan::where('is_active', true)->orderBy('sort_order')->first();

        // Create tenant record in system DB
        $tenant = Tenant::create([
            'name'              => $data['name'],
            'email'             => $data['email'],
            'phone'             => $data['phone'] ?? null,
            'subdomain'         => $subdomain,
            'database_name'     => $dbName,
            'database_host'     => env('TENANT_DB_HOST', '127.0.0.1'),
            'database_port'     => (int) env('TENANT_DB_PORT', 3306),
            'database_username' => $dbUsername,
            'database_password' => $dbPassword,   // automatically encrypted by the model mutator
            'plan_id'           => $plan?->id,
            'status'            => 'trial',
            'trial_ends_at'     => now()->addDays($plan?->trial_days ?? 14),
        ]);

        // Provision database
        $this->databaseManager->createTenantDatabase($dbName);
        $this->databaseManager->createTenantDatabaseUser($dbUsername, $dbPassword, $dbName);

        // Run migrations + seed defaults
        $this->databaseManager->runTenantMigrations($tenant, seed: true);

        return ['tenant' => $tenant];
    }

    // -------------------------------------------------------------------------
    // Deletion
    // -------------------------------------------------------------------------

    /**
     * Permanently delete a tenant and all its data.
     *
     * @param  string  $subdomain
     * @param  bool    $force  Also drop the MySQL database and user.
     */
    public function delete(string $subdomain, bool $force = false): void
    {
        $tenant = Tenant::where('subdomain', $subdomain)->firstOrFail();

        if ($force) {
            $this->databaseManager->dropTenantDatabase($tenant->database_name);
            $this->databaseManager->dropTenantDatabaseUser($tenant->database_username);
        }

        $tenant->forceDelete();
    }

    // -------------------------------------------------------------------------
    // Lookup helpers
    // -------------------------------------------------------------------------

    /**
     * Find a tenant by subdomain or return null.
     */
    public function findBySubdomain(string $subdomain): ?Tenant
    {
        return Tenant::where('subdomain', $subdomain)->first();
    }

    /**
     * Switch the active database connection to the given tenant's DB.
     */
    public function switchToTenant(Tenant $tenant): void
    {
        $this->databaseManager->connectToTenant($tenant);
        app()->instance('current_tenant', $tenant);
    }
}
