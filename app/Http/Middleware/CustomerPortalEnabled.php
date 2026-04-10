<?php

namespace App\Http\Middleware;

use App\Models\Tenant\TenantSetting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CustomerPortalEnabled
{
    public function handle(Request $request, Closure $next): Response
    {
        $enabled = TenantSetting::where('key', 'customer_portal_enabled')->value('value') === '1';

        if (!$enabled) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Customer portal is not available.'], 403);
            }

            abort(403, 'The customer portal is not currently available. Please contact your ISP.');
        }

        return $next($request);
    }
}
