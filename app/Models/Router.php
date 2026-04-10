<?php
namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Router extends Model
{
    use HasFactory, SoftDeletes;
    protected $guarded = ['id'];

    protected $casts = [
        'last_heartbeat_at' => 'datetime',
    ];

    public function subscribers()
    {
        return $this->hasMany(Subscriber::class, 'router_id');
    }

    public function nas()
    {
        return $this->hasOne(Nas::class, 'nasname', 'vpn_ip');
    }

    public function syncLogs()
    {
        return $this->hasMany(\App\Models\RouterSyncLog::class, 'router_id');
    }

    public function pendingCommands()
    {
        return $this->hasMany(\App\Models\PendingRouterCommand::class, 'router_id');
    }
}