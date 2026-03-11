<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Supported locales
    |--------------------------------------------------------------------------
    |
    | List of locale codes (e.g. 'en', 'de') that the application supports.
    | Used for URL prefix validation and locale switcher.
    |
    */

    'supported' => array_filter(explode(',', env('APP_SUPPORTED_LOCALES', 'en'))),

    /*
    |--------------------------------------------------------------------------
    | Default locale
    |--------------------------------------------------------------------------
    |
    | Used when no locale is in the URL (e.g. redirect / to /en).
    | Must be one of the supported locales.
    |
    */

    'default' => env('APP_LOCALE', 'en'),

];
