<?php

return [
    'openai_api_key'               => env('OPENAI_API_KEY'),
    'openai_model'                 => env('OPENAI_MODEL', 'gpt-4o-mini'),
    'openai_max_tokens'            => (int) env('OPENAI_MAX_TOKENS', 500),
    'openai_temperature'           => (float) env('OPENAI_TEMPERATURE', 0.7),
    'rate_limit_per_minute'        => (int) env('AI_RATE_LIMIT', 20),
    'session_history_limit'        => 10,
    'monthly_token_limit_per_tenant' => (int) env('AI_MONTHLY_TOKEN_LIMIT', 100000),
    'enabled'                      => (bool) env('AI_ENABLED', true),
    'fallback_message'             => "I'm not sure about that. Let me flag this for the support team to review. In the meantime, you can reach support directly using the chat button.",
    'portals'                      => ['guest', 'admin', 'customer', 'community', 'login'],
];
