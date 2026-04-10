<?php

return [
    'server_public_key' => env('WG_SERVER_PUBLIC_KEY', ''),
    'server_endpoint'   => env('WG_SERVER_ENDPOINT', ''),
    'port'              => (int) env('WG_PORT', 51820),
    'interface'         => env('WG_INTERFACE', 'wg0'),
];