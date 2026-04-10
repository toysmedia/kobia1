<?php

namespace App\Http\Middleware;

use App\Models\System\Tenant;
use App\Services\TenantService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * ResolveTenant middleware — runs on every web request to a tenant subdomain.
 *
 * Responsibilities:
 *  1. Extract the subdomain from the HTTP Host header.
 *  2. Look up the tenant in the system database.
 *  3. Abort 404 if tenant not found.
 *  4. Redirect to subscription payment page if tenant is suspended or expired.
 *  5. Switch the active DB connection to the tenant's database.
 *  6. Bind the resolved tenant to the IoC container as 'current_tenant'.
 */
class ResolveTenant
{
    public function __construct(
        private readonly TenantService $tenantService,
    ) {}

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $subdomain = $this->extractSubdomain($request->getHost());

        if ($subdomain === null) {
            // No subdomain present — let the request proceed (e.g. main domain or IP)
            return $next($request);
        }

        // Look up the tenant
        $tenant = $this->tenantService->findBySubdomain($subdomain);

        if ($tenant === null) {
            abort(404, 'ISP not found. Please check your subdomain.');
        }

        // Check subscription status
        if ($tenant->isSuspended()) {
            if (! $request->is('subscription*', 'payment*', 'api/mpesa*')) {
                return redirect()->route('tenant.subscription.payment')
                    ->with('error', 'Your account has been suspended. Please contact support or make a payment.');
            }
        }

        if ($tenant->isExpired()) {
            if (! $request->is('subscription*', 'payment*', 'api/mpesa*')) {
                return redirect()->route('tenant.subscription.payment')
                    ->with('error', 'Your subscription has expired. Please renew to continue.');
            }
        }

        // Switch DB connection + register tenant singleton
        $this->tenantService->switchToTenant($tenant);

        // Share tenant with all views
        view()->share('currentTenant', $tenant);

        return $next($request);
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    /**
     * Extract the leftmost subdomain segment from the given hostname.
     * Returns null when the hostname matches the root domain or is an IP.
     *
     * Examples:
     *   kenya-isp.oxnet.co.ke   → kenya-isp
     *   admin.oxnet.co.ke       → admin
     *   oxnet.co.ke             → null
     *   127.0.0.1               → null
     */
    private function extractSubdomain(string $host): ?string
    {
        // Strip port if present
        $host = strtolower(preg_replace('/:\d+$/', '', $host));

        $appDomain = strtolower(env('APP_DOMAIN', 'oxnet.co.ke'));

        // Return null for the root domain itself
        if ($host === $appDomain) {
            return null;
        }

        // Must end with the app domain
        if (! str_ends_with($host, ".{$appDomain}")) {
            return null;
        }

        $subdomain = substr($host, 0, strlen($host) - strlen(".{$appDomain}"));

        // Only return if it's a single-level subdomain (no dots)
        if (str_contains($subdomain, '.')) {
            return null;
        }

        return $subdomain ?: null;
    }
}
