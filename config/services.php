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

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
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
        'redirect' => env('GOOGLE_REDIRECT_URI'),
    ],
    'zenziva' => [
        'url' => env('ZENZIVA_URL'),
        'userkey' => env('ZENZIVA_USERKEY'),
        'passkey' => env('ZENZIVA_PASSKEY'),
    ],
    'zuwinda' => [
        'api_url' => env('ZUWINDA_SMS_URL'),
        'api_key' => env('ZUWINDA_API_KEY'),
        'default_route' => env('ZUWINDA_SMS_ROUTE', 'PREMIUM'), // Default to CHEAP if not set
        // WhatsApp
        'whatsapp_url' => env('ZUWINDA_WHATSAPP_URL', 'https://api.zuwinda.com/v2/messaging/whatsapp/message'),
        'whatsapp_account_id' => env('ZUWINDA_WHATSAPP_ACCOUNT_ID'),

    ],

];
