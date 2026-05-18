<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
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

    'whatsapp' => [
        'send_endpoint' => env('WA_API_URL_SINGLE'),
        'campaign_endpoint' => env('WA_API_URL_BULK'),
        'link_endpoint' => env('WA_API_URL_LINK'),
        'relink_endpoint' => env('WA_API_URL_RELINK'),
        'accounts_endpoint' => env('WA_API_URL_GET_ACCOUNTS'),
        'api_key' => env('WA_API_KEY'),
        'sid' => env('WA_API_SID'),
        'default_account' => env('WA_DEFAULT_ACCOUNT'),
        'allowed_accounts' => array_filter(array_map(
            'trim',
            explode(',', (string) env('WA_ALLOWED_ACCOUNTS', ''))
        )),
        'webhook_token' => env('WA_WEBHOOK_TOKEN'),
        'timeout' => env('WA_API_TIMEOUT', 30),
        'verify_ssl' => env('WA_API_VERIFY_SSL', true),
        'chatbot_welcome_message' => env(
            'WA_CHATBOT_WELCOME_MESSAGE',
            'Hola, soy el asistente virtual. Hemos recibido tu mensaje.'
        ),
    ],

];
