<?php

use App\Models\Admin\AdminUser;
use App\Models\Cliente\CustomerUser;
use App\Models\Proveedor\ProviderUser;
use App\Models\Repartidor\DriverUser;
use App\Models\User;

return [

    'defaults' => [
        'guard' => env('AUTH_GUARD', 'web'),
        'passwords' => env('AUTH_PASSWORD_BROKER', 'users'),
    ],

    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],

        'admin' => [
            'driver' => 'session',
            'provider' => 'admin_users',
        ],

        'cliente' => [
            'driver' => 'session',
            'provider' => 'customer_users',
        ],

        'proveedor' => [
            'driver' => 'session',
            'provider' => 'provider_users',
        ],

        'repartidor' => [
            'driver' => 'session',
            'provider' => 'driver_users',
        ],
    ],

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => env('AUTH_MODEL', User::class),
        ],

        'admin_users' => [
            'driver' => 'eloquent',
            'model' => AdminUser::class,
        ],

        'customer_users' => [
            'driver' => 'eloquent',
            'model' => CustomerUser::class,
        ],

        'provider_users' => [
            'driver' => 'eloquent',
            'model' => ProviderUser::class,
        ],

        'driver_users' => [
            'driver' => 'eloquent',
            'model' => DriverUser::class,
        ],
    ],

    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => env('AUTH_PASSWORD_RESET_TOKEN_TABLE', 'password_reset_tokens'),
            'expire' => 60,
            'throttle' => 60,
        ],

        'admin_users' => [
            'provider' => 'admin_users',
            'table' => 'admin_password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],

        'customer_users' => [
            'provider' => 'customer_users',
            'table' => 'customer_password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],

        'provider_users' => [
            'provider' => 'provider_users',
            'table' => 'provider_password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],

        'driver_users' => [
            'provider' => 'driver_users',
            'table' => 'driver_password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    'password_timeout' => env('AUTH_PASSWORD_TIMEOUT', 10800),

];