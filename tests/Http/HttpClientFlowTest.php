<?php

declare(strict_types=1);

namespace Tests\Http;

use PHPUnit\Framework\TestCase;
use Sahil\Contracts\Http\Client\HttpClientContract;
use Sahil\Foundation\Application;
use Sahil\Http\Http;

/**
 * End-to-end: `Http` resolves the container-bound client and the
 * `Http::fake()`/`assertSent()` flow works in a real application —
 * with zero real network traffic.
 */
final class HttpClientFlowTest extends TestCase
{
    protected function tearDown(): void
    {
        Http::flushState();
        Application::free();

        parent::tearDown();
    }

    public function test_client_resolves_through_container(): void
    {
        $app = $this->boot();

        $client = $app->container()->make(HttpClientContract::class);

        self::assertSame($client, Http::client());
    }

    public function test_fake_intercepts_and_records_without_network(): void
    {
        $this->boot();

        Http::fake(['api.example.com/*' => Http::response(['users' => [['name' => 'Ada']]])]);

        $response = Http::get('https://api.example.com/users');

        self::assertSame('Ada', $response->json()['users'][0]['name']);
        Http::assertSent(fn ($r) => $r->method === 'GET' && str_contains($r->url, 'users'));
    }

    private function boot(): Application
    {
        require_once dirname(__DIR__, 2).'/bootstrap/app.php';

        $app = \sahil_bootstrap();

        if (Application::peek() !== $app) {
            $configured = new \ReflectionProperty(Application::class, 'configured');
            $configured->setValue(null, $app);
        }

        return $app;
    }
}
