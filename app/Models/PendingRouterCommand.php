<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Queued commands to be pushed to a MikroTik router on next contact.
 *
 * @property int         $id
 * @property int         $router_id
 * @property string      $command      e.g. add_ppp_secret, remove_ppp_secret, disconnect_ppp, …
 * @property array|null  $payload      JSON payload for the command
 * @property string      $status       pending | dispatched | executed | failed
 * @property string|null $result       API response / error message
 * @property \Carbon\Carbon|null $executed_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class PendingRouterCommand extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'payload'     => 'array',
        'executed_at' => 'datetime',
    ];

    public function router()
    {
        return $this->belongsTo(Router::class);
    }
}