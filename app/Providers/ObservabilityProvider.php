<?php

declare(strict_types=1);

namespace App\Providers;

use Sahil\Foundation\CentralExceptionHandler;
use Sahil\Foundation\ServiceProvider;
use Sahil\Http\ErrorRenderer;
use Sahil\Http\Kernel;
use Sahil\Http\Request;
use Sahil\Http\Middleware\SecurityHeaders;
use Sahil\Support\Logging\FileLogger;
use Sahil\Support\Logging\LoggerContract;
use Sahil\Support\Logging\LogManager;
use Sahil\Support\Logging\StderrLogger;

/**
 * Registers observability services: the structured file logger and the
 * centralized exception handler, and installs the handler into the HTTP
 * Kernel so every uncaught throwable is both logged and rendered safely.
 *
 * Configuration this provider reads:
 * - `app.debug` (bool) — MUST be false in production (validated at boot);
 * - `app.log_level` (string, default `info`) — lowest severity kept;
 * - `app.environment` — the environment the exception handler reports in.
 */
final class ObservabilityProvider extends ServiceProvider
{
    public function register(): void
    {
        $container = $this->app()->container();

        $container->singleton(
            LogManager::class,
            static function (\Sahil\Container\Container $c): LogManager {
                $app = $c->make(\Sahil\Contracts\Foundation\ApplicationContract::class);
                $config = (array) $app->config()->get('logging', []);
                $channels = [];

                foreach ((array) ($config['channels'] ?? []) as $name => $channel) {
                    $driver = (string) ($channel['driver'] ?? 'file');

                    $channels[$name] = match ($driver) {
                        'file' => new FileLogger(
                            (string) ($channel['path'] ?? $app->storagePath('logs/app.log')),
                            (string) ($channel['level'] ?? 'info'),
                            (string) ($channel['format'] ?? 'json'),
                            (string) ($channel['name'] ?? $name),
                        ),
                        'stderr' => new StderrLogger(
                            null,
                            (string) ($channel['level'] ?? 'info'),
                            (string) ($channel['format'] ?? 'json'),
                            (string) ($channel['name'] ?? $name),
                        ),
                        'stack' => (array) ($channel['channels'] ?? []),
                        default => throw new \RuntimeException("Unknown log channel driver [{$driver}] for [{$name}]."),
                    };
                }

                // `log_level`/`app.log_level` still works: a `file` channel
                // exists even when config/logging.php is absent.
                if ($channels === []) {
                    $channels['file'] = new FileLogger(
                        $app->storagePath('logs/app.log'),
                        (string) $app->config()->get('app.log_level', 'info'),
                    );
                }

                return new LogManager($channels, (string) ($config['default'] ?? 'file'));
            }
        );

        // `LoggerContract` resolves the manager itself — shared context
        // (request ids, job scopes) applies to every injection site.
        $container->singleton(
            LoggerContract::class,
            static fn (\Sahil\Container\Container $c): LoggerContract => $c->make(LogManager::class)
        );

        $container->singleton(
            CentralExceptionHandler::class,
            static fn (\Sahil\Container\Container $c): CentralExceptionHandler
                => new CentralExceptionHandler(
                    $c->make(LoggerContract::class),
                    // Browsers get a designed HTML error page, API clients keep
                    // the JSON contract; publish the pages to customize them:
                    // `php sahil error:publish`.
                    ErrorRenderer::fromContainer($c)
                )
        );
    }

    public function boot(): void
    {
        // Attach the centralized handler to the kernel (if one is bound).
        $container = $this->app()->container();

        if ($container->has(Kernel::class)) {
            /** @var Kernel $kernel */
            $kernel = $container->make(Kernel::class);

            // Secure response headers are added for every request, before the
            // exception handler is attached so error responses get them too.
            $kernel = $kernel->using(new SecurityHeaders());

            $handler = $container->make(CentralExceptionHandler::class);
            $debug = $container->make(\Sahil\Contracts\Foundation\ApplicationContract::class)
                ->config()->get('app.debug') === true;

            $container->instance(Kernel::class, $kernel->usingExceptionHandler(
                static function (\Throwable $throwable, ?Request $request = null) use ($handler, $debug): ?\Sahil\Http\Response {
                    // Validation failures are ordinary responses — the kernel
                    // turns them into the 303/422 contract itself — not
                    // reportable errors.
                    if ($throwable instanceof \Sahil\Validation\ValidationException) {
                        return null;
                    }

                    $handler->report($throwable, $request);

                    return $handler->render($throwable, $debug, $request);
                }
            ));
        }
    }
}
