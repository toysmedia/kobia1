<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Tenant-scoped notification stored in the tenant's database.
 *
 * Types: broadcast, system_warning, feature_release, subscription_alert
 *
 * @property int $id
 * @property string $type
 * @property string $title
 * @property string $message
 * @property bool $is_read
 * @property \Carbon\Carbon|null $read_at
 * @property array|null $data
 */
class TenantNotification extends Model
{
    use SoftDeletes;

    protected $connection = 'tenant';

    protected $table = 'tenant_notifications';

    protected $fillable = [
        'type',
        'title',
        'message',
        'is_read',
        'read_at',
        'data',
    ];

    protected $casts = [
        'is_read'    => 'boolean',
        'read_at'    => 'datetime',
        'data'       => 'array',
    ];

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    public function markAsRead(): void
    {
        if (! $this->is_read) {
            $this->update(['is_read' => true, 'read_at' => now()]);
        }
    }
}
