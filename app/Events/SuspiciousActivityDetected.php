<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired when a form submission contains suspicious (test/fake) data.
 * Listeners may log to audit trail and notify the Super Admin.
 */
class SuspiciousActivityDetected
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly string $url,
        public readonly array  $violations,
        public readonly array  $inputs,
        public readonly ?int   $userId    = null,
        public readonly ?int   $tenantId  = null,
        public readonly string $ipAddress = '',
    ) {}
}
