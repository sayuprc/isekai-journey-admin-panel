<?php

declare(strict_types=1);

$adminOrigin = env('AUTH_PASSKEY_ORIGIN', env('ASSET_URL', 'https://local.admin.isekaijoucho.fan'));
$adminRpId = env('AUTH_PASSKEY_RP_ID', parse_url($adminOrigin, PHP_URL_HOST) ?: 'local.admin.isekaijoucho.fan');

return [
    /*
    |--------------------------------------------------------------------------
    | Authentication Defaults
    |--------------------------------------------------------------------------
    |
    | This option defines the default authentication "guard" and password
    | reset "broker" for your application. You may change these values
    | as required, but they're a perfect start for most applications.
    |
    */

    'defaults' => [
        'guard' => env('AUTH_GUARD', 'web'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication Guards
    |--------------------------------------------------------------------------
    |
    | Next, you may define every authentication guard for your application.
    | Of course, a great default configuration has been defined for you
    | which utilizes session storage plus the Eloquent user provider.
    |
    | All authentication guards have a user provider, which defines how the
    | users are actually retrieved out of your database or other storage
    | system used by the application. Typically, Eloquent is utilized.
    |
    | Supported: "session"
    |
    */

    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | User Providers
    |--------------------------------------------------------------------------
    |
    | All authentication guards have a user provider, which defines how the
    | users are actually retrieved out of your database or other storage
    | system used by the application. Typically, Eloquent is utilized.
    |
    | If you have multiple user tables or models you may configure multiple
    | providers to represent the model / table. These providers may then
    | be assigned to any extra authentication guards you have defined.
    |
    | Supported: "database", "eloquent"
    |
    */

    'providers' => [
        'users' => [
            'driver' => 'database',
            'table' => 'admin_users',
        ],
    ],

    'jwt' => [
        'alg' => env('AUTH_JWT_ALG', 'HS256'),
        'key' => env('AUTH_JWT_KEY'),
    ],

    'passkey' => [
        'rp_name' => env('AUTH_PASSKEY_RP_NAME', env('APP_NAME', 'IsekaiObservatory')),
        'rp_id' => $adminRpId,
        'origin' => $adminOrigin,
        'timeout_ms' => (int)env('AUTH_PASSKEY_TIMEOUT_MS', 60000),
        'ceremony_ttl_seconds' => (int)env('AUTH_PASSKEY_CEREMONY_TTL_SECONDS', 300),
        'ceremony_cache_store' => env('AUTH_PASSKEY_CEREMONY_CACHE_STORE', 'file'),
    ],
];
