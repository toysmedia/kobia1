<?php

return [
    /*
    |--------------------------------------------------------------------------
    | MikroTik Provisioning Script Settings
    |--------------------------------------------------------------------------
    |
    | These settings control the behaviour of the generated MikroTik
    | provisioning scripts (MikrotikScriptService).
    |
    */

    // Maximum seconds to wait for certificate files to be fully downloaded
    // onto the router before attempting to import them.
    // The script polls every 2 seconds; valid range: 10–600.
    'cert_download_timeout' => (int) env('MIKROTIK_CERT_DOWNLOAD_TIMEOUT', 120),
];