<?php

declare(strict_types=1);

namespace Tests\Http;

use PHPUnit\Framework\TestCase;
use Sahil\Foundation\Application;
use Sahil\Http\Kernel;
use Sahil\Http\Request;

/**
 * API resources end-to-end: the controller returns a Resource /
 * ResourceCollection and the kernel renders it through the normal
 * response path on the real booted starter app.
 */
final class ResourceFlowTest extends TestCase
{
    private function app(): Application
    {
        require_once dirname(__DIR__, 2).'/bootstrap/app.php';

        return \sahil_bootstrap();
    }

    private function get(string $path): \Sahil\Http\Response
    {
        return $this->app()->container()->make(Kernel::class)->handle(
            Request::fromGlobals(['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => $path], []),
        );
    }

    public function test_single_resource_with_nested_and_conditional_fields(): void
    {
        $res = $this->get('/api/users/1');
        self::assertSame(200, $res->status());
        self::assertSame('application/json', $res->header('Content-Type'));

        $body = json_decode($res->body(), true);
        self::assertSame('Asha', $body['name']);
        self::assertSame('asha@example.com', $body['email']); // emailVisible
        self::assertCount(2, $body['posts']);
        self::assertSame('Karibu', $body['posts'][0]['title']);
    }

    public function test_conditional_email_omitted_when_not_visible(): void
    {
        $body = json_decode($this->get('/api/users/2')->body(), true);
        self::assertSame('Baraka', $body['name']);
        self::assertArrayNotHasKey('email', $body);
        self::assertSame([], $body['posts']);
    }

    public function test_paginated_collection_envelope(): void
    {
        $body = json_decode($this->get('/api/users')->body(), true);
        self::assertCount(2, $body['data']);
        self::assertSame(2, $body['meta']['total']);
        self::assertSame(1, $body['meta']['currentPage']);
        self::assertFalse($body['meta']['hasMorePages']);
    }

    public function test_unknown_user_still_returns_the_standard_404(): void
    {
        $res = $this->get('/api/users/99');
        self::assertSame(404, $res->status());
    }

    public function test_sensitive_source_fields_never_appear(): void
    {
        // The demo records carry no password/token keys, and even if they
        // did, only toArray()'s explicit list is serialized.
        self::assertStringNotContainsString('password', $this->get('/api/users/1')->body());
        self::assertStringNotContainsString('token', $this->get('/api/users/1')->body());
    }
}
