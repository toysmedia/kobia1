<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Rejects common test/placeholder names such as "test", "asdf", "lorem", etc.
 */
class NotTestName implements ValidationRule
{
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

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || empty($value)) {
            return;
        }

        $lower = strtolower(trim($value));

        if (in_array($lower, self::BLOCKED_NAMES, true)) {
            $fail('Please use a real name.');
            return;
        }

        // Pure numeric name
        if (is_numeric($lower)) {
            $fail('Please use a real name.');
            return;
        }
    }
}
