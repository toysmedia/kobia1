<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Chat message between the tenant admin and Super Admin.
 * Stored in the tenant's database.
 *
 * @property int $id
 * @property int|null $user_id
 * @property string $message
 * @property string $sender_type  admin|super_admin
 * @property bool $is_read
 * @property \Carbon\Carbon|null $read_at
 */
class SupportMessage extends Model
{
    use SoftDeletes;

    protected $connection = 'tenant';

    protected $table = 'support_messages';

    protected $fillable = [
        'user_id',
        'message',
        'sender_type',
        'is_read',
        'read_at',
    ];

    protected $casts = [
        'is_read'  => 'boolean',
        'read_at'  => 'datetime',
    ];

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeFromAdmin($query)
    {
        return $query->where('sender_type', 'admin');
    }

    public function scopeFromSuperAdmin($query)
    {
        return $query->where('sender_type', 'super_admin');
    }
}
