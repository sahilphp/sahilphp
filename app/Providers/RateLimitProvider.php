<?php

declare(strict_types=1);

namespace App\Providers;

use Sahil\Auth\AuthManager;
use Sahil\Contracts\Cache\CacheContract;
use Sahil\Foundation\ServiceProvider;
use Sahil\Http\Middleware\Throttle;
use Sahil\RateLimit\CacheCounterStore;
use Sahil\RateLimit\CounterStore;
use Sahil\RateLimit\Identity;
use Sahil\RateLimit\Limit;
use Sahil\RateLimit\RateLimiter;
use Sahil\RateLimit\RateLimitManager;

/**
 * Rate limiting — storage and named limits.
 *
 * Bindings:
 *   - `CounterStore` → where counters live. Defaults to
 *     `CacheCounterStore` on the configured cache driver — fine for a
 *     single process. For multi-worker deployments bind a
 *     `RedisCounterStore` (atomic `INCR`/`EXPIRE`) instead:
 *
 *         $container->singleton(CounterStore::class, fn () =>
 *             new RedisCounterStore($redis));
 *
 *   - `RateLimitManager` → the engine; resolves the current user from
 *     `AuthManager` on every check so nothing leaks between requests.
 *
 * Named limits are defined in `boot()` — the `throttle:` middleware
 * references them (`throttle:login`), and `RateLimiter::hit()` works
 * anywhere.
 */
final class RateLimitProvider extends ServiceProvider
{
    public function register(): void
    {
        $container = $this->app()->container();

        $container->singleton(CounterStore::class, static fn ($c): CounterStore
            => new CacheCounterStore($c->make(CacheContract::class)));

        $keyPrefix = 'rl:'.($this->app()->config()->get('app.name') ?? 'app');

        $container->singleton(RateLimitManager::class, static fn ($c): RateLimitManager
            => new RateLimitManager(
                $c->make(CounterStore::class),
                static fn () => $c->make(AuthManager::class)->user(),
                keyPrefix: $keyPrefix,
            ));

        $container->singleton(Throttle::class, static fn ($c): Throttle
            => new Throttle($c->make(RateLimitManager::class)));
    }

    public function boot(): void
    {
        // Login: 5 attempts/minute keyed on the submitted email + client
        // IP — an attacker can't lock a victim out from another network,
        // and a botnet can't hammer one account through one source.
        RateLimiter::define('login', Limit::perMinute(5)->by(
            Identity::accountAndIp(
                static fn ($request, $user) => (string) ($request->form()['email'] ?? ''),
            ),
        ));

        // Registration and password resets: per-IP ceilings — cheap abuse
        // prevention without penalizing shared networks.
        RateLimiter::define('register', Limit::perMinute(3));
        RateLimiter::define('password.reset', Limit::perMinute(3));

        // General API ceiling, keyed on the authenticated user when there
        // is one. Tune per route with inline specs: `throttle:60,1`.
        RateLimiter::define('api', Limit::perMinute(60)->by(Identity::userOrIp()));
    }
}
