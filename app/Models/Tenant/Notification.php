<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

/**
 * In-app notification stored in the tenant's database.
 * Uses UUID primary key (matches Laravel's notification table convention).
 *
 * @property string $id
 * @property string $type
 * @property string $notifiable_type
 * @property int $notifiable_id
 * @property array $data
 * @property \Carbon\Carbon|null $read_at
 */
class Notification extends Model
{
    protected $connection = 'tenant';

    protected $table = 'notifications';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'type',
        'notifiable_type',
        'notifiable_id',
        'data',
        'read_at',
    ];

    protected $casts = [
        'data'    => 'array',
        'read_at' => 'datetime',
    ];

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    /**
     * Scope to unread notifications.
     */
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }
}
