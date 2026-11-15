<?php

declare(strict_types=1);

namespace Tests\OpenApi;

use PHPUnit\Framework\TestCase;
use Sahil\Http\Kernel;
use Sahil\Http\Request;
use Sahil\OpenApi\OpenApi;

/**
 * `/docs/openapi.json` through the real booted kernel — the spec reflects
 * the actual registered routes, declared metadata, bearer security, and
 * never leaks secrets.
 */
final class OpenApiFlowTest extends TestCase
{
    private function get(string $path): \Sahil\Http\Response
    {
        require_once dirname(__DIR__, 2) . '/bootstrap/app.php';
        \sahil_bootstrap();

        return \sahil_bootstrap()->container()->make(Kernel::class)->handle(
            Request::fromGlobals(['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => $path], []),
        );
    }

    private function spec(): array
    {
        $res = $this->get('/docs/openapi.json');
        self::assertSame(200, $res->status());
        self::assertSame('application/json', $res->header('Content-Type'));

        return json_decode($res->body(), true);
    }

    public function test_document_is_valid_openapi_31(): void
    {
        $spec = $this->spec();
        self::assertSame('3.1.0', $spec['openapi']);
        self::assertArrayHasKey('info', $spec);
        self::assertArrayHasKey('paths', $spec);
        self::assertArrayHasKey('components', $spec);
        // bearerAuth is emitted only when an operation references it — this
        // install ships no auth:api routes (auth scaffold not installed).
        self::assertArrayHasKey('PaginationMeta', $spec['components']['schemas']);
    }

    public function test_registered_routes_are_documented_and_excluded_ones_are_not(): void
    {
        $paths = $this->spec()['paths'];
        self::assertArrayHasKey('/api/users', $paths);
        self::assertArrayHasKey('/api/users/{id}', $paths);
        self::assertArrayHasKey('/api/webhook-endpoints', $paths);
        foreach (['/docs', '/docs/openapi.json', '/health', '/ui-kit', '/', '/firefiber', '/firefiber/uploads', '/firefiber/assets/{asset}'] as $p) {
            self::assertArrayNotHasKey($p, $paths);
        }
    }

    public function test_declared_metadata_params_and_resources(): void
    {
        $spec = $this->spec();
        self::assertSame('List webhook endpoints', $spec['paths']['/api/webhook-endpoints']['get']['summary']);
        $names = array_column($spec['paths']['/api/webhook-endpoints']['get']['parameters'], 'name');
        self::assertContains('filter[active]', $names);
        self::assertContains('search', $names);
        self::assertContains('perPage', $names);
        $schema = $spec['paths']['/api/users/{id}']['get']['responses']['200']['content']['application/json']['schema'];
        self::assertSame('integer', $schema['properties']['id']['type']);
    }

    public function test_no_sensitive_data_in_spec(): void
    {
        $json = json_encode($this->spec());
        self::assertStringNotContainsString('secret', $json);
        self::assertStringNotContainsString('token_hash', $json);
        self::assertStringNotContainsString('password', $json);
    }

    public function test_no_orphan_diagnostics(): void
    {
        $this->spec();
        $errors = array_filter(OpenApi::diagnostics(), fn($d) => $d['level'] === 'error');
        self::assertSame([], array_values($errors));
    }

    public function test_ui_page_loads_spec_url(): void
    {
        $res = $this->get('/docs');
        self::assertSame(200, $res->status());
        self::assertStringContainsString('/docs/openapi.json', $res->body());
    }

    public function test_disabled_docs_return_404(): void
    {
        $config = \sahil_bootstrap()->config();
        $config->set('openapi.enabled', false);

        try {
            self::assertSame(404, $this->get('/docs/openapi.json')->status());
            self::assertSame(404, $this->get('/docs')->status());
        } finally {
            $config->set('openapi.enabled', true);
        }
    }
}
