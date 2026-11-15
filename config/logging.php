<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Logging
|--------------------------------------------------------------------------
|
| Channel-based logging. `file` writes structured JSON lines (or `line`
| format for readable local output) under storage/logs; `stderr` suits
| containers and process managers; `stack` fans one call out to several
| channels. All context passes through the sensitive-key redactor.
|
*/

return [

    'default' => env('LOG_CHANNEL', 'file'),

    'channels' => [

        'file' => [
            'driver' => 'file',
            'path' => storage_path('logs/app.log'),
            'level' => env('LOG_LEVEL', 'info'),
            'format' => env('LOG_FORMAT', 'json'), // json | line
            'name' => 'file',
        ],

        'stderr' => [
            'driver' => 'stderr',
            'level' => env('LOG_LEVEL', 'info'),
            'format' => env('LOG_FORMAT', 'json'),
        ],

        // Fan-out: one log() writes to every member channel; a failing
        // member never starves the others.
        'stack' => [
            'driver' => 'stack',
            'channels' => ['file', 'stderr'],
        ],

    ],

];
