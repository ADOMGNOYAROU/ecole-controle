<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides a sane default
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

    // Smart Engage : collecte des emails des directeurs pour les campagnes (clé générée dans Smart Engage > Sites)
    'smart_engage' => [
        'url' => env('SMART_ENGAGE_URL'),
        'cle' => env('SMART_ENGAGE_CLE'),
    ],

    'paydunya' => [
        'master_key' => env('PAYDUNYA_MASTER_KEY'),
        'public_key' => env('PAYDUNYA_PUBLIC_KEY'),
        'private_key' => env('PAYDUNYA_PRIVATE_KEY'),
        'token' => env('PAYDUNYA_TOKEN'),
        'mode' => env('PAYDUNYA_MODE', 'test'),
        'recipient_name' => env('PAYDUNYA_RECIPIENT_NAME', 'joslin ADOM'),
        'recipient_phone' => env('PAYDUNYA_RECIPIENT_PHONE', '98646779'),
        'recipient_email' => env('PAYDUNYA_RECIPIENT_EMAIL', 'adomgnoyarou@gmail.com'),
        // t-money-togo ou moov-togo (voir la doc PayDunya pour les autres pays)
        'recipient_withdraw_mode' => env('PAYDUNYA_RECIPIENT_WITHDRAW_MODE', 'moov-togo'),
        'redistribution_enabled' => (bool) env('PAYDUNYA_REDISTRIBUTION_ENABLED', false),
    ],

];
