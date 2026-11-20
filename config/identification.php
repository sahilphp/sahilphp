<?php

declare(strict_types=1);

/**
 * Framework technology identification.
 *
 * Controls the public `SahilPHP` fingerprint the HTTP kernel applies to
 * responses: a `<meta name="generator">` tag inside framework-produced
 * HTML documents and the `X-Powered-By` response header. Every value can
 * be overridden through `.env`; no secret belongs here.
 */
return [
    // Master switch. `false` removes both the generator metadata and the
    // identification header from kernel responses.
    'enabled' => env('SAHIL_IDENTIFICATION', true),

    // Include the installed framework version in generator metadata
    // (`SahilPHP 1.3.0` instead of bare `SahilPHP`).
    'expose_version' => env('SAHIL_IDENTIFICATION_VERSION', true),

    // Send `X-Powered-By: SahilPHP` on every response. The header carries
    // the framework name only — never a version, PHP or server detail.
    'powered_by' => env('SAHIL_POWERED_BY', true),

    // Pin the reported version instead of resolving the installed
    // `sahilphp/framework` package version (release-shaped values only).
    'version' => env('SAHIL_IDENTIFICATION_PIN_VERSION') ?: null,
];
