<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Postmark credentials
    |--------------------------------------------------------------------------
    |
    | Here you may provide your Postmark server API token.
    |
    */

    'secret' => env('POSTMARK_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | Guzzle options
    |--------------------------------------------------------------------------
    |
    | Under the hood we use Guzzle to make API calls to Postmark.
    | Here you may provide any request options for Guzzle.
    |
    */

    'guzzle' => [
        'timeout' => 10,
        'connect_timeout' => 10,
    ],

    /*
    |--------------------------------------------------------------------------
    | Validate TLS Upgrade
    |--------------------------------------------------------------------------
    |
    | On February 16, 2021 Postmark announced some upcoming TLS
    | configuration changes for API users. Use this field to
    | change the hostname to their temporary endpoint.
    | This option will cease to work after the
    | April 13th cut off date.
    |
    | https://postmarkapp.com/updates/upcoming-tls-configuration-changes-for-api-users-action-may-be-required
    |
    */

    'validating' => [
        'tls' => env('POSTMARK_VALIDATING_TLS', false),
    ],
];
