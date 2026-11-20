<?php

declare(strict_types=1);

use App\Http\DocsController;
use App\Http\HealthController;
use App\Http\HomeController;
use App\Http\UiKitController;
use Sahil\Routing\Link;

/**
 * Route table for this installation, registered through
 * App\Providers\WebRoutesProvider during boot.
 */
return [
    Link::get('/health', [HealthController::class, 'response'])->name('health'),
    Link::get('/docs/openapi.json', [DocsController::class, 'spec'])->name('docs.openapi'),
    Link::get('/docs', [DocsController::class, 'ui'])->name('docs.ui'),
    // Versioned spec/UI — {version} resolves against the REAL route table.
    Link::get('/docs/{version}/openapi.json', [DocsController::class, 'specVersion'])->name('docs.openapi.version'),
    Link::get('/docs/{version}', [DocsController::class, 'uiVersion'])->name('docs.ui.version'),
    Link::get('/api/users', [\App\Http\UserApiController::class, 'index'])->name('api.users'),
    Link::get('/api/users/{id}', [\App\Http\UserApiController::class, 'show'])->name('api.users.show'),
    // Native URL-prefix API versioning: /api/v1 and /api/v2 alongside the
    // preserved unversioned endpoints.
    ...Link::prefix('/api')->group(fn() => [
        ...Link::version('v1', fn() => [
            Link::get('/users', [\App\Http\UserApiController::class, 'index'])->name('api.v1.users'),
            Link::get('/users/{id}', [\App\Http\UserApiController::class, 'show'])->name('api.v1.users.show'),
        ]),
        ...Link::version('v2', fn() => [
            Link::get('/users', [\App\Http\Api\V2\UserApiController::class, 'index'])->name('api.v2.users'),
            Link::get('/users/{id}', [\App\Http\Api\V2\UserApiController::class, 'show'])->name('api.v2.users.show'),
        ]),
    ]),
    Link::get('/api/webhook-endpoints', [\App\Http\WebhookApiController::class, 'index'])->name('api.webhook-endpoints'),
    Link::get('/ui-kit', [UiKitController::class, 'response'])->name('ui-kit'),
    // Firefiber pages mount directly on the route table — no controller.
    Link::firefiber('/firefiber/admin-users', \App\Firefiber\Pages\AdminUsersPage::class)->name('firefiber.admin-users'),
    Link::get('/', [HomeController::class, 'response'])->name('home'),
];
