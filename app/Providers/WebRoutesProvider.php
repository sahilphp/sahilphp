<?php

declare(strict_types=1);

namespace App\Providers;

use RuntimeException;
use Sahil\Contracts\Foundation\ApplicationContract;
use Sahil\Foundation\ServiceProvider;
use Sahil\Http\Kernel;
use Sahil\Routing\Link;
use Sahil\Http\Router;

/**
 * Registers the HTTP surface for this installation:
 * the route table (from routes/web.php and, when generated, routes/auth.php)
 * and the Kernel singleton wired with the application's debug mode.
 */
final class WebRoutesProvider extends ServiceProvider
{
    public function register(): void
    {
        $router = $this->routeTable();

        $this->app()->container()->singleton(
            Router::class,
            static fn (): Router => $router
        );

        $this->app()->container()->singleton(
            Kernel::class,
            static function (\Sahil\Container\Container $container): Kernel {
                $application = $container->make(ApplicationContract::class);

                return (new Kernel($container->make(Router::class), $container, $application))
                    ->useDebugMode($application->config()->get('app.debug') === true)
                    ->using($container->make(\Sahil\Http\Middleware\StartSession::class))
                    // Resolve the request locale (?lang=, session, Accept-Language)
                    // after sessions start so a stored preference can be read.
                    ->using($container->make(\Sahil\Http\Middleware\SetLocale::class))
                    ->using($container->make(\Sahil\Http\Middleware\VerifyCsrfToken::class))
                    ->using(new \Sahil\Http\Middleware\RequestIdMiddleware(
                        logs: $container->make(\Sahil\Support\Logging\LogManager::class),
                    ));
            }
        );
    }

    /**
     * Build the route table from `routes/web.php` and, when `unda:auth` has
     * generated it, `routes/auth.php`. Both files return route lists.
     */
    private function routeTable(): Router
    {
        $router = new Router();

        foreach (['routes/web.php', 'routes/auth.php'] as $relative) {
            $file = dirname(__DIR__, 2) . '/' . $relative;

            if (!is_file($file)) {
                if ($relative === 'routes/web.php') {
                    throw new RuntimeException(sprintf('routes/web.php is missing; expected it at [%s].', $file));
                }

                // Authentication routes are optional: a project that never ran
                // `unda:auth` boots with the web routes only.
                continue;
            }

            // `require`, not `require_once`: every freshly booted application
            // (tests, workers) needs its own route table, while `require_once`
            // returns true on the second boot in the same process.
            /** @var mixed $definitions */
            $definitions = require $file;

            if (!is_array($definitions)) {
                throw new RuntimeException(sprintf('%s must return an array of Sahil\Routing\Link objects.', $relative));
            }

            foreach ($definitions as $definition) {
                if (!$definition instanceof Link) {
                    throw new RuntimeException(
                        sprintf('%s must return Sahil\Routing\Link entries, got %s.', $relative, get_debug_type($definition))
                    );
                }

                $router->register($definition);
            }
        }

        return $router;
    }
}
