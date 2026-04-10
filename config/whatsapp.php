<?php
return [
    /*
    |--------------------------------------------------------------------------
    | WhatsApp API Gateway Configuration
    |--------------------------------------------------------------------------
    | Settings for sending WhatsApp messages via a third-party API gateway.
    | These values are overridden at runtime from the isp_settings table.
    */
    'enabled'       => env('WHATSAPP_ENABLED', false),
    'api_url'       => env('WHATSAPP_API_URL', ''),
    'instance_id'   => env('WHATSAPP_INSTANCE_ID', ''),
    'api_key'       => env('WHATSAPP_API_KEY', ''),
    'sender_number' => env('WHATSAPP_SENDER_NUMBER', ''),
];