<?php

declare(strict_types=1);

namespace Tests\Firefiber;

use App\Http\HomeController;
use PHPUnit\Framework\TestCase;
use Sahil\Foundation\Application;
use Sahil\Rendering\Render;
use Sahil\Rendering\RenderManager;
use Sahil\Routing\Link;

/**
 * Firefiber pages mount directly on the route table — `Link::firefiber()`
 * or the provider's auto-discovery — and never flow through controller
 * render helpers, so the `fire`/`fiber` render environments are gone.
 */
final class UnifiedRenderTest extends TestCase
{
    protected function tearDown(): void
    {
        Render::flushState();
        Application::free();

        parent::tearDown();
    }

    public function test_html_environment_registered_without_firefiber_envs(): void
    {
        $app = $this->boot();

        /** @var RenderManager $manager */
        $manager = $app->container()->make(RenderManager::class);

        self::assertContains('html', $manager->environments());
        self::assertNotContains('fire', $manager->environments());
        self::assertNotContains('fiber', $manager->environments());
    }

    public function test_link_firefiber_mounts_a_page_class_at_its_own_path(): void
    {
        $this->boot();

        $link = Link::firefiber(\App\Firefiber\Pages\AdminUsersPage::class)->name('test.admin-users');

        self::assertSame('/firefiber/admin-users', $link->path());
        self::assertSame([\App\Firefiber\Pages\AdminUsersPage::class, 'show'], $link->handler());
    }

    public function test_link_firefiber_accepts_an_explicit_path(): void
    {
        $this->boot();

        $link = Link::firefiber('/admin/users/{id}', \App\Firefiber\Pages\AdminUsersPage::class);

        self::assertSame('/admin/users/{id}', $link->path());
    }

    public function test_link_firefiber_rejects_non_page_classes(): void
    {
        $this->boot();

        $this->expectException(\InvalidArgumentException::class);

        Link::firefiber(HomeController::class);
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

    private function boot(): Application
    {
        require_once dirname(__DIR__, 2).'/bootstrap/app.php';

        $app = \sahil_bootstrap();

        /** @var RenderManager $manager */
        $manager = $app->container()->make(RenderManager::class);
        Render::swap($manager);

        return $app;
    }
}
