<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Cache Driver
|--------------------------------------------------------------------------
|
| This option controls the cache driver that will be used to store cached
| data for the application. By default, we will use the file cache driver
| which stores the cache in the storage/framework/cache directory.
|
| Supported: file, redis, database, memcached, dynamodb
|
*/

return [

    'default' => env('CACHE_DRIVER', 'file'),

    'stores' => [

        'file' => [
            'driver' => 'file',
            'path' => storage_path('framework/cache'),
        ],

        'redis' => [
            'driver' => 'redis',
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'port' => (int) env('REDIS_PORT', 6379),
            'password' => env('REDIS_PASSWORD', null),
            'database' => (int) env('REDIS_DB', 0),
            // Key namespace inside a shared Redis database — set a unique
            // prefix per application so keys and flush() stay isolated.
            'prefix' => env('CACHE_PREFIX', 'sahil:cache:'),
        ],

    ],

];