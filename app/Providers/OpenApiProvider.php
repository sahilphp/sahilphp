<?php

declare(strict_types=1);

namespace App\Providers;

use App\Http\Resources\UserResource;
use App\Http\Resources\WebhookEndpointResource;
use Sahil\Contracts\Foundation\ApplicationContract;
use Sahil\Foundation\ServiceProvider;
use Sahil\OpenApi\OpenApi;

/**
 * OpenAPI configuration + endpoint metadata. The route TABLE is the
 * source of truth — these `describe()` calls only add what can't be
 * inferred (summaries, request rules, response schemas).
 */
final class OpenApiProvider extends ServiceProvider
{
    public function register(): void
    {
        $config = $this->app()->config();
        $exact = [];
        $prefixes = [];
        foreach ((array) $config->get('openapi.exclude_paths', []) as $pattern) {
            str_ends_with((string) $pattern, '*')
                ? $prefixes[] = rtrim((string) $pattern, '*')
                : $exact[(string) $pattern] = true;
        }

        OpenApi::configure([
            'title' => $config->get('openapi.title', 'SahilPHP API'),
            'description' => $config->get('openapi.description'),
            'version' => $config->get('openapi.version', '1.0.0'),
            'servers' => (array) $config->get('openapi.servers', []),
            'license' => $config->get('openapi.license'),
            'license_url' => $config->get('openapi.license_url'),
            'tag_descriptions' => (array) $config->get('openapi.tag_descriptions', []),
            'ui_library_url' => $config->get('openapi.ui_library_url') ?: null,
            'exclude' => static function ($route) use ($exact, $prefixes): bool {
                $path = $route->path();
                if (isset($exact[$path])) {
                    return true;
                }
                foreach ($prefixes as $prefix) {
                    if (str_starts_with($path, $prefix)) {
                        return true;
                    }
                }

                return false;
            },
        ]);

        OpenApi::describe('api.users', [
            'summary' => 'List demo users',
            'tags' => ['Users'],
            'responses' => [200 => [
                'resource' => UserResource::class, 'collection' => true, 'paginated' => true,
            ]],
        ]);

        OpenApi::describe('api.users.show', [
            'summary' => 'Show a demo user',
            'tags' => ['Users'],
            'pathParams' => ['id' => ['schema' => ['type' => 'integer'], 'description' => 'User id']],
            'responses' => [200 => ['resource' => UserResource::class], 404 => ['description' => 'Not found']],
        ]);

        // Versioned routes — V1 keeps the original representation.
        OpenApi::describe('api.v1.users', [
            'summary' => 'List users (v1)',
            'tags' => ['Users'],
            'responses' => [200 => [
                'resource' => UserResource::class, 'collection' => true, 'paginated' => true,
            ]],
        ]);

        OpenApi::describe('api.v1.users.show', [
            'summary' => 'Show a user (v1)',
            'tags' => ['Users'],
            'pathParams' => ['id' => ['schema' => ['type' => 'integer'], 'description' => 'User id']],
            'responses' => [200 => ['resource' => UserResource::class], 404 => ['description' => 'Not found']],
        ]);

        // V2 — the evolved shape, via its own resource + apiSchema().
        OpenApi::describe('api.v2.users', [
            'summary' => 'List users (v2)',
            'tags' => ['Users'],
            'responses' => [200 => [
                'resource' => \App\Http\Resources\V2\UserResource::class, 'collection' => true, 'paginated' => true,
            ]],
        ]);

        OpenApi::describe('api.v2.users.show', [
            'summary' => 'Show a user (v2)',
            'tags' => ['Users'],
            'pathParams' => ['id' => ['schema' => ['type' => 'integer'], 'description' => 'User id']],
            'responses' => [200 => [
                'resource' => \App\Http\Resources\V2\UserResource::class,
            ], 404 => ['description' => 'Not found']],
        ]);

        OpenApi::describe('api.webhook-endpoints', [
            'summary' => 'List webhook endpoints',
            'tags' => ['Webhooks'],
            'apiQuery' => [
                'filters' => ['active', 'url'], 'sorts' => ['url', 'created_at', 'active'],
                'searchable' => ['url'], 'fields' => ['id', 'url', 'events', 'active', 'created_at'],
                'perPage' => 15, 'maxPerPage' => 100,
            ],
            'responses' => [200 => [
                'resource' => WebhookEndpointResource::class, 'collection' => true, 'paginated' => true,
            ]],
        ]);
    }
}
