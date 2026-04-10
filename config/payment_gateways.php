<?php
return [
    /*
    |--------------------------------------------------------------------------
    | Payment Gateways Configuration
    |--------------------------------------------------------------------------
    | Each gateway can be enabled/disabled and configured independently.
    | Runtime settings are stored in the isp_settings key-value table and
    | override these defaults.
    */

    'mpesa_paybill' => [
        'enabled'      => env('PG_MPESA_PAYBILL_ENABLED', false),
        'number'       => env('PG_MPESA_PAYBILL_NUMBER', ''),
        'account'      => env('PG_MPESA_PAYBILL_ACCOUNT', ''),
        'display_name' => env('PG_MPESA_PAYBILL_DISPLAY', 'M-Pesa Paybill'),
    ],

    'mpesa_till' => [
        'enabled'      => env('PG_MPESA_TILL_ENABLED', false),
        'number'       => env('PG_MPESA_TILL_NUMBER', ''),
        'display_name' => env('PG_MPESA_TILL_DISPLAY', 'M-Pesa Till'),
    ],

    'kopokopo' => [
        'enabled'       => env('PG_KOPOKOPO_ENABLED', false),
        'client_id'     => env('PG_KOPOKOPO_CLIENT_ID', ''),
        'client_secret' => env('PG_KOPOKOPO_CLIENT_SECRET', ''),
        'till'          => env('PG_KOPOKOPO_TILL', ''),
        'webhook_url'   => env('PG_KOPOKOPO_WEBHOOK_URL', ''),
        'environment'   => env('PG_KOPOKOPO_ENV', 'sandbox'),
    ],

    'equity' => [
        'enabled'       => env('PG_EQUITY_ENABLED', false),
        'merchant_id'   => env('PG_EQUITY_MERCHANT_ID', ''),
        'api_key'       => env('PG_EQUITY_API_KEY', ''),
        'account'       => env('PG_EQUITY_ACCOUNT', ''),
        'callback_url'  => env('PG_EQUITY_CALLBACK_URL', ''),
    ],

    'kcb' => [
        'enabled'       => env('PG_KCB_ENABLED', false),
        'merchant_code' => env('PG_KCB_MERCHANT_CODE', ''),
        'api_key'       => env('PG_KCB_API_KEY', ''),
        'account'       => env('PG_KCB_ACCOUNT', ''),
        'callback_url'  => env('PG_KCB_CALLBACK_URL', ''),
    ],

    'coop' => [
        'enabled'          => env('PG_COOP_ENABLED', false),
        'consumer_key'     => env('PG_COOP_CONSUMER_KEY', ''),
        'consumer_secret'  => env('PG_COOP_CONSUMER_SECRET', ''),
        'account'          => env('PG_COOP_ACCOUNT', ''),
        'callback_url'     => env('PG_COOP_CALLBACK_URL', ''),
    ],
];