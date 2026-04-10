<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * EnsureSubscriptionActive middleware — enforces subscription status on all
 * tenant-facing routes after the tenant has already been resolved.
 *
 * Features:
 *  - Locks all routes (except the payment page) if subscription has expired.
 *  - Passes a countdown warning to views if expiry is within 7 days.
 */
class EnsureSubscriptionActive
{
    /** Number of days before expiry to start showing the warning banner. */
    private const WARNING_DAYS = 7;

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! app()->bound('current_tenant')) {
            return $next($request);
        }

        /** @var \App\Models\System\Tenant $tenant */
        $tenant = app('current_tenant');

        // Allow access to subscription/payment routes regardless of status
        if ($request->is('subscription*', 'payment*', 'api/mpesa*')) {
            return $next($request);
        }

        if ($tenant->isExpired() || $tenant->isSuspended()) {
            return redirect()->route('tenant.subscription.payment')
                ->with('error', 'Your subscription is inactive. Please renew to continue.');
        }

        // Inject countdown warning into views when expiry is imminent
        $daysLeft = $tenant->daysUntilExpiry();
        if ($daysLeft <= self::WARNING_DAYS && $daysLeft >= 0) {
            view()->share('subscriptionExpiryDays', $daysLeft);
            view()->share('subscriptionExpiresAt', $tenant->subscription_expires_at ?? $tenant->trial_ends_at);
        }

        return $next($request);
    }
}
