<?php

declare(strict_types=1);

namespace Tests\ApiVersioning;

use PHPUnit\Framework\TestCase;
use Sahil\Http\Kernel;
use Sahil\Http\Request;
use Sahil\Http\Response;

/**
 * Native API versioning through the real booted kernel: `Link::version`
 * routes dispatch to the right representation, unregistered versions are
 * plain 404s, and per-version OpenAPI documents filter by actual route
 * metadata.
 */
final class ApiVersioningFlowTest extends TestCase
{
    private function get(string $path): Response
    {
        require_once dirname(__DIR__, 2) . '/bootstrap/app.php';

        return \sahil_bootstrap()->container()->make(Kernel::class)->handle(
            Request::fromGlobals(
                ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => $path, 'HTTP_ACCEPT' => 'application/json'],
                [],
            ),
        );
    }

    public function test_v1_serves_the_original_representation(): void
    {
        $res = $this->get('/api/v1/users');
        self::assertSame(200, $res->status());
        $user = json_decode($res->body(), true)['data'][0];
        self::assertArrayHasKey('name', $user);
        self::assertArrayHasKey('posts', $user);
        self::assertArrayNotHasKey('posts_count', $user);
    }

    public function test_v2_serves_the_evolved_representation(): void
    {
        $res = $this->get('/api/v2/users');
        self::assertSame(200, $res->status());
        $user = json_decode($res->body(), true)['data'][0];
        // V2: name→display_name, embedded posts→posts_count, email removed.
        self::assertSame('Asha', $user['display_name']);
        self::assertSame('@asha', $user['handle']);
        self::assertSame(2, $user['posts_count']);
        self::assertArrayNotHasKey('email', $user);
        self::assertArrayNotHasKey('posts', $user);
    }

    public function test_v2_show_route_parameters_still_work(): void
    {
        $res = $this->get('/api/v2/users/2');
        self::assertSame(200, $res->status());
        $user = json_decode($res->body(), true);
        self::assertSame('Baraka', $user['display_name']);
    }

    public function test_unversioned_endpoints_are_preserved(): void
    {
        $res = $this->get('/api/users');
        self::assertSame(200, $res->status());
        self::assertArrayHasKey('name', json_decode($res->body(), true)['data'][0]);
    }

    public function test_unregistered_version_never_falls_back(): void
    {
        self::assertSame(404, $this->get('/api/v9/users')->status());
        self::assertSame(404, $this->get('/api/v3/users/1')->status());
    }

    public function test_versioned_spec_lists_only_its_own_paths(): void
    {
        $v1 = json_decode($this->get('/docs/v1/openapi.json')->body(), true);
        self::assertSame('v1', $v1['x-api-version']);
        self::assertSame('v1', $v1['info']['version']);
        self::assertSame(['/api/v1/users', '/api/v1/users/{id}'], array_keys($v1['paths']));

        $v2 = json_decode($this->get('/docs/v2/openapi.json')->body(), true);
        self::assertSame('v2', $v2['x-api-version']);
        $schema = $v2['paths']['/api/v2/users']['get']['responses']['200']['content']['application/json']['schema'];
        $props = $schema['properties']['data']['items']['properties'];
        self::assertArrayHasKey('display_name', $props);
        self::assertArrayNotHasKey('email', $props);
    }

    public function test_unregistered_version_spec_is_a_404(): void
    {
        self::assertSame(404, $this->get('/docs/v9/openapi.json')->status());
        self::assertSame(404, $this->get('/docs/bogus/openapi.json')->status());
    }

    public function test_versioned_ui_points_at_its_own_spec(): void
    {
        $res = $this->get('/docs/v2');
        self::assertSame(200, $res->status());
        self::assertStringContainsString('/docs/v2/openapi.json', $res->body());
    }

    public function test_default_spec_lists_everything_versioned_or_not(): void
    {
        $spec = json_decode($this->get('/docs/openapi.json')->body(), true);
        $paths = array_keys($spec['paths']);
        self::assertContains('/api/users', $paths);
        self::assertContains('/api/v1/users', $paths);
        self::assertContains('/api/v2/users', $paths);
        self::assertArrayNotHasKey('x-api-version', $spec);
    }
}
