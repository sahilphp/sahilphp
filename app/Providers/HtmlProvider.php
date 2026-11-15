<?php

declare(strict_types=1);

namespace App\Providers;

use Sahil\Container\Container;
use Sahil\Contracts\View\ViewContract;
use Sahil\Foundation\ServiceProvider;
use Sahil\Rendering\Adapters\HtmlAdapter;
use Sahil\Rendering\RenderManager;

/**
 * Registers the native `html` rendering environment on the core
 * {@see RenderManager}. Every project needs it — the base controllers
 * render `.sahil.php` templates through `$this->html(...)`.
 *
 * UI kits re-bind `RenderManager` with their own adapter set (their
 * providers are registered after this one, so their binding wins).
 */
final class HtmlProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app()->container()->singleton(
            RenderManager::class,
            static function (Container $container): RenderManager {
                /** @var ViewContract $views */
                $views = $container->make(ViewContract::class);

                return new RenderManager([new HtmlAdapter($views)]);
            },
        );
    }
}
