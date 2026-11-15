<?php

declare(strict_types=1);

namespace Tests\Firefiber;

use App\Http\HomeController;
use PHPUnit\Framework\TestCase;
use Sahil\Foundation\Application;
use Sahil\Rendering\Render;
use Sahil\Rendering\RenderManager;

/**
 * End-to-end: the Firefiber provider's RenderManager binding carries both
 * the native `html` adapter and Firefiber's `fire` adapter, and a real
 * controller renders a real page through the unified API.
 */
final class UnifiedRenderTest extends TestCase
{
    protected function tearDown(): void
    {
        Render::flushState();
        Application::free();

        parent::tearDown();
    }

    public function test_fire_and_html_environments_registered(): void
    {
        $app = $this->boot();

        /** @var RenderManager $manager */
        $manager = $app->container()->make(RenderManager::class);

        self::assertContains('html', $manager->environments());
        self::assertContains('fire', $manager->environments());
    }

    public function test_fire_renders_real_component_markup(): void
    {
        $this->boot();

        $response = Render::fire('AdminUsersTable', ['search' => 'a']);

        self::assertSame(200, $response->status());
        self::assertStringContainsString('text/html', (string) $response->header('Content-Type'));
        self::assertStringContainsString('data-firefiber-component', $response->body());
    }

    public function test_controller_html_helper_renders_real_template(): void
    {
        $app = $this->boot();

        $controller = new HomeController($app);
        $response = $controller->response();

        self::assertSame(200, $response->status());
        self::assertStringContainsString('SahilPHP', $response->body());
        self::assertSame('pages.home', $response->renderedView()?->template());
    }

    public function test_response_fire_factory_through_app_manager(): void
    {
        $this->boot();

        $response = \Sahil\Http\Response::fire('AdminUsersTable', ['page' => 1]);

        self::assertSame('fire', $response->frontend());
        self::assertStringContainsString('data-firefiber-component', $response->body());
        self::assertSame('AdminUsersTable', $response->renderedView()?->template());
    }

    private function boot(): Application
    {
        require_once dirname(__DIR__, 2).'/bootstrap/app.php';

        $app = \sahil_bootstrap();

        // `sahil_bootstrap()` caches its instance, so the process-configured
        // handle may be gone after another test's `Application::free()` —
        // point Render at this app's manager explicitly.
        /** @var RenderManager $manager */
        $manager = $app->container()->make(RenderManager::class);
        Render::swap($manager);

        return $app;
    }
}
