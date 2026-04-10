<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CustomerSubscriptionActive
{
    /**
     * Route names that expired/suspended subscribers are still allowed to access.
     */
    private const ALLOWED_ROUTE_NAMES = [
        'customer.payments.renew',
        'customer.payments.process',
        'customer.logout',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        /** @var \App\Models\Subscriber|null $subscriber */
        $subscriber = auth('customer')->user();

        if (!$subscriber) {
            return $next($request);
        }

        if ($subscriber->isExpired() || $subscriber->status === 'suspended') {
            $currentRoute = $request->route()?->getName();

            if (in_array($currentRoute, self::ALLOWED_ROUTE_NAMES, true)) {
                return $next($request);
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Your subscription has expired. Please renew to continue.',
                ], 403);
            }

            return redirect()->route('customer.payments.renew')
                ->with('warning', 'Your subscription has expired. Please renew to continue.');
        }

        return $next($request);
    }
}
