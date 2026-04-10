<?php

namespace App\Listeners;

use App\Events\SuspiciousActivityDetected;
use App\Models\System\SystemAuditLog;

/**
 * Logs a SuspiciousActivityDetected event to the system audit log.
 */
class LogSuspiciousActivity
{
    public function handle(SuspiciousActivityDetected $event): void
    {
        try {
            SystemAuditLog::record(
                action: 'suspicious_activity_detected',
                newValues: [
                    'url'        => $event->url,
                    'violations' => $event->violations,
                    'inputs'     => $event->inputs,
                    'ip_address' => $event->ipAddress,
                ],
                tenantId: $event->tenantId,
            );
        } catch (\Throwable) {
            // Never let logging failure break the request
        }
    }
}
