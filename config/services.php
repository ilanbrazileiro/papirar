<?php

return [

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'mercadopago' => [
        'base_url' => env('MERCADO_PAGO_BASE_URL', 'https://api.mercadopago.com'),
        'access_token' => env('MERCADO_PAGO_ACCESS_TOKEN'),
        'public_key' => env('MERCADO_PAGO_PUBLIC_KEY'),
        'webhook_url' => env('MERCADO_PAGO_WEBHOOK_URL'),
        'webhook_secret' => env('MERCADO_PAGO_WEBHOOK_SECRET'),
    ],
    
    'gpt_api' => [
        'token' => env('GPT_API_TOKEN'),
    ],

    'analytics' => [
        'ga4_measurement_id' => env('GA4_MEASUREMENT_ID'),
        'ga4_property_id' => env('GA4_PROPERTY_ID'),
        'ga4_credentials_path' => env('GA4_CREDENTIALS_PATH'),
        'search_console_verification' => env('GOOGLE_SITE_VERIFICATION'),
    ],

    'marketing_gpt_api' => [
        'token' => env('MARKETING_GPT_API_TOKEN'),
    ],
];
