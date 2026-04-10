<?php

namespace App\Http\Middleware;

use App\Models\System\SystemAuditLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * AuditAction middleware — logs every state-mutating HTTP request to the
 * system audit log, capturing the authenticated user, tenant context,
 * request method, URI, and request payload.
 *
 * Apply to admin and super-admin route groups.
 */
class AuditAction
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only audit state-changing requests that succeeded
        if (
            in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true) &&
            $response->getStatusCode() < 400
        ) {
            $this->log($request);
        }

        return $response;
    }

    // -------------------------------------------------------------------------
    // Private
    // -------------------------------------------------------------------------

    private function log(Request $request): void
    {
        try {
            SystemAuditLog::record(
                action: strtolower($request->method()) . ':' . $request->path(),
                newValues: $request->except(['password', 'password_confirmation', '_token']),
            );
        } catch (\Throwable) {
            // Never let audit logging break the request
        }
    }
}
