<?php

declare(strict_types=1);

namespace Tests\Storage;

use PHPUnit\Framework\TestCase;
use Sahil\Foundation\Application;
use Sahil\Storage\Kapu;

/**
 * End-to-end: Kapu resolves the app's configured `local` disk
 * (`storage/app`) and performs real writes, reads and cleanup through
 * the container-bound StorageContract.
 */
final class KapuFlowTest extends TestCase
{
    private const PATH = 'kapu-test/example.txt';

    protected function tearDown(): void
    {
        // Leave the app's storage clean regardless of outcome.
        try {
            Kapu::deleteDirectory('kapu-test');
        } catch (\Throwable) {
        }

        Kapu::flushState();
        Application::free();

        parent::tearDown();
    }

    public function test_default_disk_round_trip_through_real_app(): void
    {
        $this->boot();

        Kapu::put(self::PATH, 'Hello from SahilPHP');

        self::assertTrue(Kapu::exists(self::PATH));
        self::assertSame('Hello from SahilPHP', Kapu::get(self::PATH));
        self::assertSame(['kapu-test/example.txt'], Kapu::files('kapu-test'));

        self::assertTrue(Kapu::delete(self::PATH));
        self::assertTrue(Kapu::missing(self::PATH));
    }

    public function test_traversal_cannot_leave_the_disk_root(): void
    {
        $this->boot();

        $this->expectException(\Sahil\Storage\StorageException::class);
        Kapu::put('../../escape.txt', 'x');
    }

    private function boot(): Application
    {
        require_once dirname(__DIR__, 2).'/bootstrap/app.php';

        $app = \sahil_bootstrap();

        // `sahil_bootstrap()` caches its instance — another test's
        // `Application::free()` may have dropped the configured handle
        // facades resolve through.
        if (Application::peek() !== $app) {
            $configured = new \ReflectionProperty(Application::class, 'configured');
            $configured->setValue(null, $app);
        }

        return $app;
    }
}
