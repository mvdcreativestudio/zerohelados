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

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'mercadopago' => [
      'public_key' => env('MERCADOPAGO_PUBLIC_KEY'),
      'access_token' => env('MERCADOPAGO_ACCESS_TOKEN'),
      'secret_key' => env('MERCADOPAGO_SECRET_KEY'),
    ],

    'google' => [
        'maps_api_key' => env('GOOGLE_MAPS_API_KEY'),
    ],

    'pedidosya' => [
        'api_key' => env('PEDIDOS_YA_API_KEY'),
    ],

    'checkout' => [
        'return_url' => env('CHECKOUT_RETURN_URL'),
    ],

    'flavorUnit' => [
        'unit' => 5,
    ],
];
