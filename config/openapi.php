<?php

declare(strict_types=1);

/**
 * Native OpenAPI documentation — generated from the REAL route table,
 * never a hand-maintained spec. `describe()` keys are route names.
 */
return [
    'title' => env('APP_NAME', 'SahilPHP') . ' API',
    'description' => 'Auto-generated from the registered route table.',
    'version' => '1.0.0',
    'servers' => [(string) env('APP_URL', 'http://localhost:8080')],
    'license' => env('OPENAPI_LICENSE', 'MIT'),
    'license_url' => env('OPENAPI_LICENSE_URL', 'https://opensource.org/licenses/MIT'),
    // Optional longer descriptions for tags used in describe() metadata.
    'tag_descriptions' => [
        'Users' => 'Demo user directory — API resources with explicit field exposure.',
        'Webhooks' => 'Outbound webhook endpoint registry — paginated, filterable, searchable.',
    ],
    // Routes deliberately excluded from the document.
    // Routes deliberately excluded — exact paths, or '/prefix/*' wildcards.
    // '/firefiber*' covers Firefiber's internal action/asset/upload plumbing.
    'exclude_paths' => ['/docs*', '/ui-kit', '/', '/health', '/firefiber*'],
    // The docs UI ships a self-contained viewer; opt into full Swagger UI
    // by pointing this at a vendored swagger-ui-dist directory URL.
    'ui_library_url' => env('OPENAPI_UI_LIBRARY', ''),
    'enabled' => env('OPENAPI_ENABLED', 'true') !== 'false',
];
