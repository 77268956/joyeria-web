<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Resend, Postmark, AWS, and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
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

    /*
    |--------------------------------------------------------------------------
    | Metal price provider
    |--------------------------------------------------------------------------
    |
    | Daily spot prices for gold and silver are fetched through an abstract
    | provider. Select the active one with METAL_PRICE_PROVIDER. Spot prices
    | arrive per troy ounce in `currency`; `exchange_rate` converts them to the
    | store's local currency before being stored per gram (see PricingCalculator).
    |
    */

    'metal_prices' => [
        'provider' => env('METAL_PRICE_PROVIDER', 'goldapi'),
        'currency' => env('METAL_PRICE_CURRENCY', 'USD'),
        'exchange_rate' => env('METAL_PRICE_EXCHANGE_RATE', '1'),
        'goldapi' => [
            'key' => env('GOLDAPI_KEY'),
            'endpoint' => env('GOLDAPI_ENDPOINT', 'https://www.goldapi.io/api'),
        ],
        'metals_dev' => [
            'key' => env('METALS_DEV_KEY'),
            'endpoint' => env('METALS_DEV_ENDPOINT', 'https://api.metals.dev/v1/latest'),
        ],
    ],

];
