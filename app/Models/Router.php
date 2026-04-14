<?php
namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Router extends Model
{
    use HasFactory, SoftDeletes;

    const CONNECTION_TYPE_DIRECT  = 'direct';
    const CONNECTION_TYPE_VPN     = 'vpn';
    const CONNECTION_TYPE_HOTSPOT = 'hotspot';

    const CONNECTION_TYPES = [
        self::CONNECTION_TYPE_DIRECT  => 'Direct API',
        self::CONNECTION_TYPE_VPN     => 'VPN Tunnel',
        self::CONNECTION_TYPE_HOTSPOT => 'Hotspot (RADIUS Only)',
    ];

    protected $guarded = ['id'];

    protected $casts = [
        'last_heartbeat_at' => 'datetime',
        'last_sync_at'      => 'datetime',
        'last_sync_stats'   => 'array',
        'connection_type'   => 'string',
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

    /**
     * Type A — Direct: MikroTik has a public IP.
     * Connect via RouterOS API directly and register in FreeRADIUS.
     */
    public function isDirectApi(): bool
    {
        return ($this->connection_type ?? self::CONNECTION_TYPE_DIRECT) === self::CONNECTION_TYPE_DIRECT;
    }

    /**
     * Type B — VPN: MikroTik is behind NAT.
     * Connects to OpenVPN server and gets a VPN tunnel IP.
     * Use that tunnel IP for both RouterOS API and FreeRADIUS NAS registration.
     */
    public function isVpn(): bool
    {
        return $this->connection_type === self::CONNECTION_TYPE_VPN;
    }

    /**
     * Type C — Hotspot: RADIUS only.
     * No RouterOS API calls are made to this router.
     */
    public function isHotspot(): bool
    {
        return $this->connection_type === self::CONNECTION_TYPE_HOTSPOT;
    }

    /**
     * Whether this router supports API calls (Direct or VPN).
     */
    public function supportsApi(): bool
    {
        return !$this->isHotspot();
    }

    /**
     * Get the IP address to use for API connections and NAS registration.
     * For direct: use wan_ip
     * For vpn: use vpn_ip
     * For hotspot: use wan_ip (for NAS only, no API calls)
     */
    public function getNasIp(): ?string
    {
        if ($this->isVpn()) {
            return $this->vpn_ip;
        }

        return $this->wan_ip ?: $this->vpn_ip;
    }
}
