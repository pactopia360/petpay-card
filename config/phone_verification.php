<?php

return [
    'provider' => env(
        'PHONE_VERIFICATION_PROVIDER',
        'fake'
    ),

    'fake_code' => env(
        'PHONE_VERIFICATION_FAKE_CODE',
        '123456'
    ),

    'code_ttl_minutes' => (int) env(
        'PHONE_VERIFICATION_CODE_TTL_MINUTES',
        10
    ),

    'cooldown_seconds' => (int) env(
        'PHONE_VERIFICATION_COOLDOWN_SECONDS',
        60
    ),

    'max_code_attempts' => (int) env(
        'PHONE_VERIFICATION_MAX_CODE_ATTEMPTS',
        5
    ),

    'max_phone_hourly' => (int) env(
        'PHONE_VERIFICATION_MAX_PHONE_HOURLY',
        3
    ),

    'max_driver_daily' => (int) env(
        'PHONE_VERIFICATION_MAX_DRIVER_DAILY',
        5
    ),

    'block_minutes' => (int) env(
        'PHONE_VERIFICATION_BLOCK_MINUTES',
        30
    ),
];
