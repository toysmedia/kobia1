<?php

namespace App\Providers;

use App\Models\System\Tenant;
use App\Services\DatabaseManager;
use App\Services\TenantService;
use Illuminate\Support\ServiceProvider;

/**
 * TenantServiceProvider — registers the multi-tenancy infrastructure into
 * the Laravel service container at application boot time.
 *
 * Responsibilities:
 *  - Register TenantService and DatabaseManager as singletons.
 *  - Register the 'current_tenant' binding (resolved per request by ResolveTenant middleware).
 *  - Boot any tenant-specific configuration when a tenant is active.
 */
class TenantServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // DatabaseManager singleton — shared across the request lifecycle
        $this->app->singleton(DatabaseManager::class);

        // TenantService singleton — uses DatabaseManager internally
        $this->app->singleton(TenantService::class, function ($app) {
            return new TenantService($app->make(DatabaseManager::class));
        });

        // 'current_tenant' is bound to null by default.
        // The ResolveTenant middleware will replace this with the resolved Tenant model.
        $this->app->bind('current_tenant', fn () => null);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Nothing to boot at the provider level — tenant-specific boot
        // happens inside the ResolveTenant middleware after DB switching.
    }
}
