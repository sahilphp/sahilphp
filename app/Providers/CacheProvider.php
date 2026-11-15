<?php

declare(strict_types=1);

namespace App\Providers;

use Sahil\Cache\FileCache;
use Sahil\Cache\RedisCache;
use Sahil\Contracts\Foundation\ApplicationContract;
use Sahil\Contracts\Cache\CacheContract;
use Sahil\Foundation\ServiceProvider;

/**
 * Registers the cache service.
 *
 * Binds `Sahil\Contracts\Cache\CacheContract` to a cache driver instance,
 * configured via `config/cache.php`.
 *
 * Supported drivers: file, redis
 *
 * To use a different driver, set `CACHE_DRIVER` env variable or
 * override the binding after this provider has been registered.
 */
final class CacheProvider extends ServiceProvider
{
    public function register(): void
    {
        $config = $this->app()->config()->get('cache', []);

        $default = $config['default'] ?? 'file';
        $stores  = $config['stores']  ?? [];

        $driver = $stores[$default]['driver'] ?? 'file';

        // Build cache instance based on driver
        switch (strtolower($driver)) {
            case 'redis':
                // Fail clearly rather than silently degrading to file —
                // a configured-but-unavailable Redis is a real defect.
                $store = $stores[$default];
                $cache = new RedisCache([
                    'host' => $store['host'] ?? '127.0.0.1',
                    'port' => (int) ($store['port'] ?? 6379),
                    'password' => $store['password'] ?? null,
                    'database' => (int) ($store['database'] ?? 0),
                    'prefix' => (string) ($store['prefix'] ?? 'sahil:cache:'),
                ]);
                break;

            case 'file':
            default:
                $cachePath = $stores[$default]['path'] ?? storage_path('framework/cache');
                $cache = new FileCache($cachePath);
                break;
        }

        $this->app()->container()->singleton(
            CacheContract::class,
            static fn (): CacheContract => $cache
        );
    }
}