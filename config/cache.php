<?php

return [
    'default' => env('CACHE_DRIVER', 'file'),

    'drivers' => [
        'file' => [
            'driver' => 'file',
            'path' => 'storage/cache',
        ],

        'redis' => [
            'driver' => 'redis',
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'port' => env('REDIS_PORT', '6379'),
            'password' => env('REDIS_PASSWORD', null),
            'database' => env('REDIS_CACHE_DB', 1),
            'prefix' => env('REDIS_PREFIX', 'neophp_cache:'),
        ],
    ],
];
