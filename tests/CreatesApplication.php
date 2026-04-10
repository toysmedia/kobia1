<?php

namespace Tests;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;

trait CreatesApplication
{
    /**
     * Creates the application.
     *
     * In the SQLite test environment (DB_CONNECTION=sqlite) all three database
     * connections ('sqlite', 'mysql', 'tenant') are mapped to the same SQLite
     * file via config/database.php.  This lets RefreshDatabase manage all tables
     * in a single place using the standard migrate:fresh + transaction strategy.
     *
     * The file path is resolved to an absolute path here so that all connections
     * (including the named 'mysql' and 'tenant' connections) point to the same
     * physical file regardless of working directory.
     */
    public function createApplication(): Application
    {
        $app = require __DIR__.'/../bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();

        if ($app['config']->get('database.default') === 'sqlite') {
            $dbPath = $app['config']->get('database.connections.sqlite.database');

            if ($dbPath && $dbPath !== ':memory:') {
                // Resolve relative path to absolute
                if (! str_starts_with($dbPath, '/')) {
                    $dbPath = base_path($dbPath);
                }

                $dir = dirname($dbPath);
                if (! is_dir($dir)) {
                    mkdir($dir, 0755, true);
                }

                // Ensure the file exists (touch without wiping — RefreshDatabase /
                // migrate:fresh handles resetting state on first run per process).
                if (! file_exists($dbPath)) {
                    touch($dbPath);
                }

                // Point all connections to the same resolved absolute path.
                $app['config']->set('database.connections.sqlite.database', $dbPath);
                $app['config']->set('database.connections.mysql.database', $dbPath);
                $app['config']->set('database.connections.tenant.database', $dbPath);
            }
        }

        return $app;
    }
}
