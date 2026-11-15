<?php

declare(strict_types=1);

namespace Tests\Http;

use PHPUnit\Framework\TestCase;
use Sahil\Http\Kernel;
use Sahil\Http\Request;

/**
 * `/api/webhook-endpoints` through the real booted kernel: ApiQuery
 * pagination, allowlisted filters, search, sorts, field selection — and
 * the webhook `secret` never reaching the wire.
 */
final class ApiQueryFlowTest extends TestCase
{
    private function get(string $path, array $query = []): \Sahil\Http\Response
    {
        require_once dirname(__DIR__, 2) . '/bootstrap/app.php';

        return \sahil_bootstrap()->container()->make(Kernel::class)->handle(
            Request::fromGlobals(['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => $path, 'HTTP_ACCEPT' => 'application/json'], $query),
        );
    }

    private function body(\Sahil\Http\Response $res): array
    {
        return json_decode($res->body(), true);
    }

    public function test_paginated_listing_with_meta(): void
    {
        $res = $this->get('/api/webhook-endpoints');
        self::assertSame(200, $res->status());
        $body = $this->body($res);
        self::assertArrayHasKey('data', $body);
        self::assertGreaterThanOrEqual(1, $body['meta']['total']);
        self::assertSame(1, $body['meta']['currentPage']);
        self::assertSame('hooks.example.test', parse_url($body['data'][0]['url'], PHP_URL_HOST));
        // secret column never serializes
        self::assertStringNotContainsString('secret', $res->body());
    }

    public function test_filter_and_search(): void
    {
        $hit = $this->body($this->get('/api/webhook-endpoints', [
            'filter' => ['active' => '1'], 'search' => 'hooks.example',
        ]));
        self::assertSame(1, $hit['meta']['total']);

        $miss = $this->body($this->get('/api/webhook-endpoints', [
            'filter' => ['active' => '0'],
        ]));
        self::assertSame(0, $miss['meta']['total']);
        self::assertSame([], $miss['data']);
    }

    public function test_unknown_filter_returns_422_not_500(): void
    {
        $res = $this->get('/api/webhook-endpoints', ['filter' => ['secret' => 'x']]);
        self::assertSame(422, $res->status());
        // no SQL / column internals in the error body
        self::assertStringNotContainsString('SELECT', $res->body());
    }

    public function test_field_selection_narrows_representation(): void
    {
        $body = $this->body($this->get('/api/webhook-endpoints', ['fields' => 'id,url,secret']));
        self::assertSame(['id', 'url'], array_keys($body['data'][0]));
    }

    public function test_injection_attempt_is_bound(): void
    {
        $res = $this->get('/api/webhook-endpoints', [
            'filter' => ['url' => ['like' => "' OR '1'='1"]],
        ]);
        self::assertSame(200, $res->status());
        self::assertSame(0, $this->body($res)['meta']['total']);
    }
}
