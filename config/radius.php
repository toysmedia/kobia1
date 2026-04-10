<?php
return [
    'server_ip' => env('RADIUS_SERVER_IP', '127.0.0.1'),
    'secret' => env('RADIUS_SECRET', 'testing123'),
    'auth_port' => env('RADIUS_AUTH_PORT', 1812),
    'acct_port' => env('RADIUS_ACCT_PORT', 1813),
    'coa_port' => env('RADIUS_COA_PORT', 3799),
    'interim_update' => 600, // 10 minutes in seconds
];
