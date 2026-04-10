<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Rejects known test/throwaway email addresses and domains.
 */
class NotTestEmail implements ValidationRule
{
    private const BLOCKED_DOMAINS = [
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

    private const BLOCKED_EXACT = [
        'admin@admin.com',
        'test@test.com',
        'user@user.com',
        'demo@demo.com',
    ];

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || empty($value)) {
            return;
        }

        $lower = strtolower(trim($value));

        if (in_array($lower, self::BLOCKED_EXACT, true)) {
            $fail('Please use a real email address.');
            return;
        }

        if (str_contains($lower, '+test')) {
            $fail('Please use a real email address.');
            return;
        }

        foreach (self::BLOCKED_DOMAINS as $domain) {
            if (str_contains($lower, $domain)) {
                $fail('Please use a real email address.');
                return;
            }
        }
    }
}
