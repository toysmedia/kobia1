<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * System-wide audit log entry spanning all tenants.
 * Immutable — no updates or soft deletes.
 *
 * @property int $id
 * @property string|null $user_type
 * @property int|null $user_id
 * @property int|null $tenant_id
 * @property string $action
 * @property string|null $model_type
 * @property int|null $model_id
 * @property array|null $old_values
 * @property array|null $new_values
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property \Carbon\Carbon $created_at
 */
class SystemAuditLog extends Model
{
    public const UPDATED_AT = null;

    protected $connection = 'mysql';

    protected $table = 'system_audit_logs';

    protected $fillable = [
        'user_type',
        'user_id',
        'tenant_id',
        'action',
        'model_type',
        'model_id',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime',
    ];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    /**
     * The tenant this log entry is associated with (nullable).
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }

    // -------------------------------------------------------------------------
    // Static helpers
    // -------------------------------------------------------------------------

    /**
     * Quickly record an audit log entry from anywhere in the application.
     */
    public static function record(
        string $action,
        ?string $modelType = null,
        int|string|null $modelId = null,
        array $oldValues = [],
        array $newValues = [],
        ?int $tenantId = null,
    ): self {
        $request = request();

        return static::create([
            'user_type'  => auth()->user() ? get_class(auth()->user()) : null,
            'user_id'    => auth()->id(),
            'tenant_id'  => $tenantId ?? optional(app()->bound('current_tenant') ? app('current_tenant') : null)?->id,
            'action'     => $action,
            'model_type' => $modelType,
            'model_id'   => $modelId,
            'old_values' => $oldValues ?: null,
            'new_values' => $newValues ?: null,
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
        ]);
    }
}
