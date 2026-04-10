<?php

namespace App\Services;

use App\Models\System\Tenant;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

/**
 * DatabaseManager handles all dynamic database operations for the multi-tenant system.
 * Responsible for creating, switching, dropping, and migrating tenant databases.
 */
class DatabaseManager
{
    /**
     * Switch the 'tenant' DB connection to the given tenant's database.
     * All models using the 'tenant' connection will now query this tenant's DB.
     */
    public function connectToTenant(Tenant $tenant): void
    {
        $config = $tenant->databaseConfig();

        Config::set('database.connections.tenant', $config);

        // Purge the cached connection so the new config takes effect immediately
        DB::purge('tenant');
        DB::reconnect('tenant');
    }

    /**
     * Switch back to the system (main) database connection.
     */
    public function connectToSystem(): void
    {
        DB::setDefaultConnection('mysql');
    }

    /**
     * Create a new MySQL database for a tenant.
     * Uses the root credentials configured in TENANT_DB_ROOT_* env variables.
     *
     * @param  string  $databaseName  The name of the database to create.
     */
    public function createTenantDatabase(string $databaseName): void
    {
        $this->withRootConnection(function ($pdo) use ($databaseName) {
            $safe = $this->sanitizeDatabaseName($databaseName);
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$safe}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        });
    }

    /**
     * Create a dedicated MySQL user restricted to a specific tenant database.
     *
     * @param  string  $username      MySQL username to create.
     * @param  string  $password      Plain-text password (will not be stored here).
     * @param  string  $databaseName  The database this user should have access to.
     */
    public function createTenantDatabaseUser(string $username, string $password, string $databaseName): void
    {
        $this->withRootConnection(function ($pdo) use ($username, $password, $databaseName) {
            $safeDb   = $this->sanitizeDatabaseName($databaseName);
            $safeUser = $this->sanitizeDatabaseName($username);

            // Create the user (DROP first if exists to allow re-provisioning)
            $pdo->exec("DROP USER IF EXISTS '{$safeUser}'@'localhost'");
            $pdo->exec("DROP USER IF EXISTS '{$safeUser}'@'%'");

            $pdo->exec("CREATE USER '{$safeUser}'@'%' IDENTIFIED BY '{$password}'");
            $pdo->exec("GRANT ALL PRIVILEGES ON `{$safeDb}`.* TO '{$safeUser}'@'%'");
            $pdo->exec("FLUSH PRIVILEGES");
        });
    }

    /**
     * Drop a tenant's MySQL database.
     *
     * @param  string  $databaseName  The database to drop.
     */
    public function dropTenantDatabase(string $databaseName): void
    {
        $this->withRootConnection(function ($pdo) use ($databaseName) {
            $safe = $this->sanitizeDatabaseName($databaseName);
            $pdo->exec("DROP DATABASE IF EXISTS `{$safe}`");
        });
    }

    /**
     * Drop the MySQL user associated with a tenant database.
     *
     * @param  string  $username  The MySQL username to drop.
     */
    public function dropTenantDatabaseUser(string $username): void
    {
        $this->withRootConnection(function ($pdo) use ($username) {
            $safeUser = $this->sanitizeDatabaseName($username);
            $pdo->exec("DROP USER IF EXISTS '{$safeUser}'@'%'");
            $pdo->exec("FLUSH PRIVILEGES");
        });
    }

    /**
     * Run all tenant-specific migrations on the tenant's database.
     * Migrations are sourced from database/migrations/tenant/.
     *
     * @param  Tenant  $tenant  The tenant to run migrations for.
     * @param  bool    $seed    Whether to also run the TenantDefaultSeeder.
     * @param  bool    $fresh   Whether to drop all tables and re-run from scratch.
     */
    public function runTenantMigrations(Tenant $tenant, bool $seed = false, bool $fresh = false): void
    {
        $this->connectToTenant($tenant);

        $command = $fresh ? 'migrate:fresh' : 'migrate';

        $options = [
            '--database' => 'tenant',
            '--path'     => 'database/migrations/tenant',
            '--force'    => true,
        ];

        if ($seed) {
            $options['--seed'] = true;
            $options['--seeder'] = 'TenantDefaultSeeder';
        }

        Artisan::call($command, $options);
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    /**
     * Execute a callback with a PDO connection using the root credentials.
     * The root connection is used only for DDL operations (CREATE/DROP database/user).
     *
     * @param  callable(\PDO): void  $callback
     */
    private function withRootConnection(callable $callback): void
    {
        $host     = env('TENANT_DB_HOST', '127.0.0.1');
        $port     = env('TENANT_DB_PORT', '3306');
        $username = env('TENANT_DB_ROOT_USERNAME', 'root');
        $password = env('TENANT_DB_ROOT_PASSWORD', '');

        $dsn = "mysql:host={$host};port={$port}";
        $pdo = new \PDO($dsn, $username, $password, [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
        ]);

        $callback($pdo);
    }

    /**
     * Sanitise a database/username string to only contain safe characters.
     * Prevents SQL injection in DDL statements that cannot use prepared statements.
     */
    private function sanitizeDatabaseName(string $name): string
    {
        // Only allow alphanumeric, underscores, and hyphens
        return preg_replace('/[^a-zA-Z0-9_\-]/', '', $name);
    }
}
