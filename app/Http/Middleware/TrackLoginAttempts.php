<?php

namespace App\Http\Middleware;

use App\Models\System\SystemAuditLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

/**
 * TrackLoginAttempts middleware — monitors authentication failures and locks
 * out the IP after 5 consecutive failed attempts within 15 minutes.
 *
 * Every lockout and suspicious pattern is logged to the system audit log
 * so Super Admin is immediately aware.
 */
class TrackLoginAttempts
{
    /** Maximum allowed failed attempts before lockout. */
    private const MAX_ATTEMPTS = 5;

    /** Lockout duration in minutes. */
    private const DECAY_MINUTES = 15;

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $key = $this->throttleKey($request);

        if ($this->isLockedOut($key)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Too many login attempts. Please try again later.',
                ], 429);
            }

            $seconds = $this->secondsUntilUnlock($key);

            return back()->withErrors([
                'email' => "Too many failed login attempts. Please try again in {$seconds} seconds.",
            ]);
        }

        $response = $next($request);

        // If authentication failed (redirect back with errors on the login route)
        if ($this->isFailedLoginResponse($request, $response)) {
            $this->incrementAttempts($key, $request);
        } elseif ($this->isSuccessfulLogin($response)) {
            // Clear attempts on successful authentication
            Cache::forget($key);
        }

        return $response;
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function throttleKey(Request $request): string
    {
        return 'login_attempts:' . sha1(strtolower($request->input('email', '')) . '|' . $request->ip());
    }

    private function isLockedOut(string $key): bool
    {
        return Cache::has("{$key}:lockout");
    }

    private function secondsUntilUnlock(string $key): int
    {
        return (int) Cache::get("{$key}:lockout_seconds", 0);
    }

    private function incrementAttempts(string $key, Request $request): void
    {
        $attempts = (int) Cache::get($key, 0) + 1;
        Cache::put($key, $attempts, now()->addMinutes(self::DECAY_MINUTES));

        if ($attempts >= self::MAX_ATTEMPTS) {
            $lockoutSeconds = self::DECAY_MINUTES * 60;
            Cache::put("{$key}:lockout", true, now()->addSeconds($lockoutSeconds));
            Cache::put("{$key}:lockout_seconds", $lockoutSeconds, now()->addSeconds($lockoutSeconds));

            $this->logLockout($request, $attempts);
        }
    }

    private function isFailedLoginResponse(Request $request, Response $response): bool
    {
        return $request->is('*/login') &&
               in_array($request->method(), ['POST'], true) &&
               ($response->getStatusCode() === 302 || $response->getStatusCode() === 422);
    }

    private function isSuccessfulLogin(Response $response): bool
    {
        return $response->getStatusCode() === 302 &&
               ! str_contains((string) $response->headers->get('Location', ''), 'login');
    }

    private function logLockout(Request $request, int $attempts): void
    {
        try {
            SystemAuditLog::record(
                action: 'login_lockout',
                newValues: [
                    'email'    => $request->input('email'),
                    'ip'       => $request->ip(),
                    'attempts' => $attempts,
                    'url'      => $request->fullUrl(),
                ],
            );
        } catch (\Throwable) {
            // Never let logging failure break the request
        }
    }
}
