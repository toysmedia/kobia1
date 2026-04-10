<?php

return [
    /*
    |--------------------------------------------------------------------------
    | OpenVPN Management Tunnel
    |--------------------------------------------------------------------------
    |
    | These settings are used to generate MikroTik provisioning scripts that
    | establish an OpenVPN tunnel back to the billing server.
    |
    */

    // Default OpenVPN port (non-standard to avoid ISP blocking)
    'port' => (int) env('OPENVPN_PORT', 443),

    // Public IP or hostname of the billing server.
    // MikroTik routers connect to this address to establish the OpenVPN tunnel.
    'billing_server_public_ip' => env('BILLING_SERVER_PUBLIC_IP', ''),

    // VPN IP of the billing server (assigned by OpenVPN, e.g. 10.8.0.1).
    // Used as the RADIUS server address in generated scripts.
    'billing_server_vpn_ip' => env('BILLING_SERVER_VPN_IP', ''),

    // Subnet of the billing server (CIDR notation, e.g. 10.8.0.0/24).
    // Used in firewall rules to allow management traffic from the billing server.
    'billing_server_subnet' => env('BILLING_SERVER_SUBNET', ''),

    // OpenVPN cipher — must use full name on RouterOS 7.x.
    // RouterOS 6.x uses 'aes256'; RouterOS 7.x requires 'aes256-cbc' or 'aes256-gcm'.
    'cipher' => env('OPENVPN_CIPHER', 'aes256-cbc'),

    // HMAC authentication algorithm used for the OpenVPN data channel.
    'auth' => env('OPENVPN_AUTH', 'sha1'),

    // Transport protocol. RouterOS ONLY supports TCP for OpenVPN clients.
    'protocol' => env('OPENVPN_PROTOCOL', 'tcp'),

    // TLS protocol version negotiation.  'any' allows the widest compatibility.
    'tls_version' => env('OPENVPN_TLS_VERSION', 'any'),

    // How often (RouterOS interval string) the MikroTik sends a sync POST.
    // Default: 1 second — change to e.g. '00:00:05' for slower routers.
    'sync_interval' => env('MIKROTIK_SYNC_INTERVAL', '00:00:01'),

    // Default sync interval (seconds) stored in the routers table.
    // Admins can override per router; minimum recommended is 1s on capable hardware.
    'default_sync_interval' => (int) env('ROUTER_SYNC_INTERVAL', 5),

    // How often the heartbeat scheduler fires to report the VPN IP.
    'heartbeat_interval' => env('MIKROTIK_HEARTBEAT_INTERVAL', '00:00:01'),

    // Seconds to wait (retry loop) for the OVPN tunnel to establish
    // before sending the Phase 1 callback. Default: 60 seconds.
    'vpn_wait_timeout' => (int) env('MIKROTIK_VPN_WAIT_TIMEOUT', 60),
];