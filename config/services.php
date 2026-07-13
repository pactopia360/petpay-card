<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    */

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

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect_cliente' => env('GOOGLE_REDIRECT_CLIENTE_URL'),
        'redirect_comercio' => env('GOOGLE_REDIRECT_COMERCIO_URL'),
        'redirect_repartidor' => env('GOOGLE_REDIRECT_REPARTIDOR_URL'),

        'maps_browser_key' => env('GOOGLE_MAPS_BROWSER_KEY', ''),
        'maps_api_key' => env('GOOGLE_MAPS_API_KEY', ''),
    ],

    /*
    |--------------------------------------------------------------------------
    | OpenAI - análisis documental PETPAY
    |--------------------------------------------------------------------------
    |
    | La clave permanece solamente en .env.
    | Nunca debe escribirse directamente en controladores, servicios o vistas.
    |
    */

    'openai' => [
        'enabled' => env('OPENAI_DOCUMENT_ANALYSIS_ENABLED', false),
        'key' => env('OPENAI_API_KEY'),
        'model' => env('OPENAI_DOCUMENT_MODEL', 'gpt-4.1-mini'),
        'base_url' => env('OPENAI_BASE_URL', 'https://api.openai.com/v1'),
        'timeout' => (int) env('OPENAI_TIMEOUT', 90),
        'connect_timeout' => (int) env('OPENAI_CONNECT_TIMEOUT', 15),
        'max_image_bytes' => (int) env(
            'OPENAI_DOCUMENT_MAX_IMAGE_BYTES',
            10485760
        ),
    ],

];