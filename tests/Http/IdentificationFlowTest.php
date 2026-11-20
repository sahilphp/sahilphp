<?php

declare(strict_types=1);

namespace Tests\Http;

use PHPUnit\Framework\TestCase;
use Sahil\Foundation\Application;
use Sahil\Http\Kernel;
use Sahil\Http\Request;
use Sahil\Http\Response;

/**
 * Technology identification end-to-end on the booted starter app
 * (docs/IDENTIFICATION.md): the kernel applies the `identification`
 * namespace — generator metadata in HTML documents and the
 * `X-Powered-By: SahilPHP` header on every response.
 */
final class IdentificationFlowTest extends TestCase
{
    private function app(): Application
    {
        require_once dirname(__DIR__, 2).'/bootstrap/app.php';

        return \sahil_bootstrap();
    }

    private function get(string $path): Response
    {
        return $this->app()->container()->make(Kernel::class)->handle(
            Request::fromGlobals(['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => $path], []),
        );
    }

    public function test_home_page_carries_the_generator_signature(): void
    {
        $response = $this->get('/');

        self::assertSame(200, $response->status());
        self::assertSame('SahilPHP', $response->header('X-Powered-By'));
        self::assertMatchesRegularExpression(
            '/<meta name="generator" content="SahilPHP(?: \d+\.\d+(?:\.\d+)?[^"]*)?">/',
            $response->body(),
        );
        self::assertSame(
            1,
            substr_count($response->body(), 'name="generator"'),
            'exactly one generator declaration',
        );
    }

    public function test_json_endpoints_carry_only_the_header(): void
    {
        $response = $this->get('/health');

        self::assertSame('application/json', $response->header('Content-Type'));
        self::assertSame('SahilPHP', $response->header('X-Powered-By'));
        self::assertStringNotContainsString('generator', $response->body());
    }

    public function test_error_pages_are_identified_too(): void
    {
        $response = $this->get('/no-such-identification-probe');

        self::assertSame(404, $response->status());
        self::assertSame('SahilPHP', $response->header('X-Powered-By'));
        self::assertStringContainsString('name="generator"', $response->body());
    }

    public function test_powered_by_can_be_disabled_independently(): void
    {
        $config = $this->app()->config();
        $previous = $config->get('identification.powered_by');
        $config->set('identification.powered_by', false);

        try {
            $response = $this->get('/health');
        } finally {
            $config->set('identification.powered_by', $previous);
        }

        self::assertNull($response->header('X-Powered-By'));
    }

    public function test_identification_can_be_disabled_entirely(): void
    {
        $config = $this->app()->config();
        $previous = $config->get('identification.enabled');
        $config->set('identification.enabled', false);

        try {
            $response = $this->get('/');
        } finally {
            $config->set('identification.enabled', $previous);
        }

        self::assertNull($response->header('X-Powered-By'));
        self::assertStringNotContainsString('name="generator"', $response->body());
    }
}
