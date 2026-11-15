<?php

declare(strict_types=1);

namespace App\Http;

use Sahil\Contracts\Foundation\ApplicationContract;
use Sahil\Http\Concerns\RendersPages;
use Sahil\Http\Response;
use Sahil\Ui\ProfileDetector;

/**
 * Renders the welcome page (`resources/views/pages/home.sahil.php`).
 *
 * This is the unified rendering API in one call: hand `$this->html()` a
 * template name and the data the template may read, and it returns the
 * rendered page as a `Response`. The template cannot reach anything that
 * is not in `$data`, and every value it interpolates is escaped unless the
 * template explicitly opts out.
 *
 * The page shows the official SahilPHP logo and the installed UI profile, so a
 * fresh project tells the user what is already wired up.
 */
final class HomeController
{
    use RendersPages;

    public function __construct(
        private readonly ApplicationContract $application,
    ) {
    }

    public function response(): Response
    {
        [$profile] = (new ProfileDetector())->detect((string) $this->application->basePath());

        return $this->html('pages.home', [
            'app_name' => (string) $this->application->config()->get('app.name', 'SahilPHP'),
            'php_version' => PHP_VERSION,
            'profile_label' => $profile?->label() ?? 'Plain CSS profile',
        ]);
    }
}
