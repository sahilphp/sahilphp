<?php

declare(strict_types=1);

namespace App\Providers;

use Sahil\Container\Container;
use Sahil\Contracts\Http\Client\HttpClientContract;
use Sahil\Foundation\ServiceProvider;
use Sahil\Http\Client\CurlTransport;
use Sahil\Http\Client\HttpClient;

/**
 * Binds the outbound HTTP client. `Http::` resolves through this
 * binding; inject `HttpClientContract` where DI is preferred. The
 * transport is cURL with TLS verification always on.
 */
final class HttpClientProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app()->container()->singleton(
            HttpClientContract::class,
            static fn (Container $container): HttpClientContract => new HttpClient(
                new CurlTransport(),
            ),
        );
    }
}
