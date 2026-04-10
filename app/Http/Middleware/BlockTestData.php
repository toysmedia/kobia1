<?php

namespace App\Http\Middleware;

use App\Models\System\SystemAuditLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * BlockTestData middleware — inspects form inputs for test/fake data patterns
 * and blocks the submission, logging the attempt and alerting Super Admin.
 *
 * Blocked patterns:
 *  - Test email domains: @test.*, @example.*, @mailinator.*, @guerrillamail.*, @tempmail.*
 *  - Test email prefixes: admin@admin.com, +test
 *  - Test phone numbers: 0700000000, +25470000000X, sequential, all-same-digit
 *  - Test names: test, asdf, qwerty, lorem ipsum, numeric-only
 */
class BlockTestData
{
    /** @var array<int, string> Blocked email domains (partial match). */
    private const BLOCKED_EMAIL_DOMAINS = [
        '@test.',
        '@example.',
        '@mailinator.',
        '@guerrillamail.',
        '@tempmail.',
        '@throwam.',
        '@yopmail.',
        '@sharklasers.',
        '@guerrillamailblock.',
        '@spam4.',
        '@dispostable.',
        '@trashmail.',
    ];

    /** @var array<int, string> Blocked exact email addresses. */
    private const BLOCKED_EMAILS = [
        'admin@admin.com',
        'test@test.com',
        'user@user.com',
        'demo@demo.com',
    ];

    /** @var array<int, string> Blocked phone numbers (exact match after normalisation). */
    private const BLOCKED_PHONES = [
        '0700000000',
        '+254700000000',
        '0712345678',
        '+254712345678',
        '0700000001',
        '0700000002',
    ];

    /** @var array<int, string> Blocked name patterns (case-insensitive). */
    private const BLOCKED_NAMES = [
        'test',
        'testing',
        'asdf',
        'asdfgh',
        'qwerty',
        'qwertyuiop',
        'lorem ipsum',
        'lorem',
        'ipsum',
        'aaaaaa',
        'abc',
        'abcdef',
        'foo',
        'bar',
        'baz',
        'foobar',
    ];

    /** @var array<int, string> Input field names that contain email values. */
    private const EMAIL_FIELDS = ['email', 'email_address', 'admin_email'];

    /** @var array<int, string> Input field names that contain phone values. */
    private const PHONE_FIELDS = ['phone', 'mobile', 'phone_number', 'mobile_number'];

    /** @var array<int, string> Input field names that contain name values. */
    private const NAME_FIELDS = ['name', 'first_name', 'last_name', 'full_name', 'company', 'isp_name'];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only inspect state-changing requests
        if (! in_array($request->method(), ['POST', 'PUT', 'PATCH'], true)) {
            return $next($request);
        }

        $violations = $this->detectViolations($request);

        if (! empty($violations)) {
            $this->logViolation($request, $violations);

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Submission rejected: test or fake data detected.',
                    'errors'  => $violations,
                ], 422);
            }

            return back()
                ->withInput()
                ->withErrors(['_test_data' => 'Please use a real email address, phone number, and name.']);
        }

        return $next($request);
    }

    // -------------------------------------------------------------------------
    // Detection helpers
    // -------------------------------------------------------------------------

    /**
     * Scan the request inputs for test data violations.
     *
     * @return array<string, string>  Field name → reason
     */
    private function detectViolations(Request $request): array
    {
        $violations = [];

        foreach (self::EMAIL_FIELDS as $field) {
            $value = $request->input($field);
            if ($value && $this->isTestEmail($value)) {
                $violations[$field] = "'{$value}' appears to be a test email address.";
            }
        }

        foreach (self::PHONE_FIELDS as $field) {
            $value = $request->input($field);
            if ($value && $this->isTestPhone($value)) {
                $violations[$field] = "'{$value}' appears to be a test phone number.";
            }
        }

        foreach (self::NAME_FIELDS as $field) {
            $value = $request->input($field);
            if ($value && $this->isTestName($value)) {
                $violations[$field] = "'{$value}' appears to be a test name.";
            }
        }

        return $violations;
    }

    private function isTestEmail(string $email): bool
    {
        $email = strtolower(trim($email));

        if (in_array($email, self::BLOCKED_EMAILS, true)) {
            return true;
        }

        // Check +test prefix
        if (str_contains($email, '+test')) {
            return true;
        }

        foreach (self::BLOCKED_EMAIL_DOMAINS as $domain) {
            if (str_contains($email, $domain)) {
                return true;
            }
        }

        return false;
    }

    private function isTestPhone(string $phone): bool
    {
        $normalised = preg_replace('/[\s\-\(\)]/', '', $phone);

        if (in_array($normalised, self::BLOCKED_PHONES, true)) {
            return true;
        }

        // Check for repeated single digit: 0000000000, 1111111111, etc.
        $digitsOnly = preg_replace('/\D/', '', $normalised);
        if (strlen($digitsOnly) >= 7 && preg_match('/^(\d)\1+$/', $digitsOnly)) {
            return true;
        }

        // Check for sequential ascending digits (e.g. 0123456789, wrapping 9→0 with modulo)
        if (strlen($digitsOnly) >= 8) {
            $isSequential = true;
            for ($i = 1; $i < strlen($digitsOnly); $i++) {
                if ((int) $digitsOnly[$i] !== ((int) $digitsOnly[$i - 1] + 1) % 10) {
                    $isSequential = false;
                    break;
                }
            }
            if ($isSequential) {
                return true;
            }
        }

        return false;
    }

    private function isTestName(string $name): bool
    {
        $lower = strtolower(trim($name));

        if (in_array($lower, self::BLOCKED_NAMES, true)) {
            return true;
        }

        // Pure numeric name
        if (is_numeric($lower)) {
            return true;
        }

        return false;
    }

    // -------------------------------------------------------------------------
    // Logging
    // -------------------------------------------------------------------------

    /**
     * Log the test-data violation to the system audit log.
     */
    private function logViolation(Request $request, array $violations): void
    {
        try {
            SystemAuditLog::record(
                action: 'test_data_blocked',
                newValues: [
                    'url'        => $request->fullUrl(),
                    'violations' => $violations,
                    'inputs'     => $request->except(['password', 'password_confirmation']),
                ],
            );
        } catch (\Throwable) {
            // Never let logging failure break the request
        }
    }
}
