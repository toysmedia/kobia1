<?php
return [
    /*
    |--------------------------------------------------------------------------
    | Active SMS Gateway
    |--------------------------------------------------------------------------
    | Supported: "africastalking", "blessedafrica", "advanta"
    */
    'driver' => env('SMS_DRIVER', 'africastalking'),

    'africastalking' => [
        'username'   => env('AT_USERNAME', 'sandbox'),
        'api_key'    => env('AT_API_KEY', ''),
        'sender_id'  => env('AT_SENDER_ID', ''),
        'base_url'   => env('AT_ENV', 'sandbox') === 'production'
            ? 'https://api.africastalking.com/version1'
            : 'https://api.sandbox.africastalking.com/version1',
    ],

    'blessedafrica' => [
        'api_key'   => env('BLESSEDAFRICA_API_KEY', ''),
        'api_url'   => env('BLESSEDAFRICA_API_URL', 'https://api.blessedafrica.co.ke/sms'),
        'sender_id' => env('BLESSEDAFRICA_SENDER_ID', ''),
    ],

    'advanta' => [
        'api_key'    => env('ADVANTA_API_KEY', ''),
        'partner_id' => env('ADVANTA_PARTNER_ID', ''),
        'sender_id'  => env('ADVANTA_SENDER_ID', ''),
        'api_url'    => env('ADVANTA_API_URL', 'https://quicksms.advantasms.com/api/services/sendSMS/'),
    ],
];
