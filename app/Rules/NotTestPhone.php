<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Rejects sequential, all-same-digit, and known test phone numbers.
 */
class NotTestPhone implements ValidationRule
{
    private const BLOCKED_EXACT = [
        '0700000000',
        '+254700000000',
        '0712345678',
        '+254712345678',
        '0700000001',
        '0700000002',
    ];

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || empty($value)) {
            return;
        }

        $normalised = preg_replace('/[\s\-\(\)]/', '', $value);

        if (in_array($normalised, self::BLOCKED_EXACT, true)) {
            $fail('Please use a real phone number.');
            return;
        }

        $digitsOnly = preg_replace('/\D/', '', $normalised);

        // All same digit: 0000000000, 1111111111, etc.
        if (strlen($digitsOnly) >= 7 && preg_match('/^(\d)\1+$/', $digitsOnly)) {
            $fail('Please use a real phone number.');
            return;
        }

        // Sequential ascending digits: 0123456789
        if (strlen($digitsOnly) >= 8) {
            $isSequential = true;
            for ($i = 1; $i < strlen($digitsOnly); $i++) {
                if ((int) $digitsOnly[$i] !== ((int) $digitsOnly[$i - 1] + 1) % 10) {
                    $isSequential = false;
                    break;
                }
            }
            if ($isSequential) {
                $fail('Please use a real phone number.');
                return;
            }
        }
    }
}
