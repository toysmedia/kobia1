<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Snapshot of a router's active sessions and resource stats, captured on each
 * 1-second sync cycle.
 *
 * @property int         $id
 * @property int         $router_id
 * @property array|null  $ppp_sessions   Active PPPoE sessions JSON
 * @property array|null  $hs_sessions    Active Hotspot sessions JSON
 * @property string|null $cpu_load
 * @property \Carbon\Carbon $synced_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class RouterSyncLog extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'ppp_sessions' => 'array',
        'hs_sessions'  => 'array',
        'synced_at'    => 'datetime',
    ];

    public function router()
    {
        return $this->belongsTo(Router::class);
    }
}