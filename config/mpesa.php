<?php
return [
    'env' => env('MPESA_ENV', 'sandbox'),
    'consumer_key' => env('MPESA_CONSUMER_KEY', ''),
    'consumer_secret' => env('MPESA_CONSUMER_SECRET', ''),
    'shortcode' => env('MPESA_SHORTCODE', '174379'),
    'passkey' => env('MPESA_PASSKEY', ''),
    'callback_url' => env('MPESA_CALLBACK_URL', ''),
    'c2b_shortcode' => env('MPESA_C2B_SHORTCODE', env('MPESA_SHORTCODE', '')),
    'c2b_validation_url' => env('MPESA_C2B_VALIDATION_URL', ''),
    'c2b_confirmation_url' => env('MPESA_C2B_CONFIRMATION_URL', ''),
    'base_url' => env('MPESA_ENV', 'sandbox') === 'production'
        ? 'https://api.safaricom.co.ke'
        : 'https://sandbox.safaricom.co.ke',
    'verify_ip' => env('MPESA_VERIFY_IP', true),
];
