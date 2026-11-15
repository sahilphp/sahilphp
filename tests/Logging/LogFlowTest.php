<?php

declare(strict_types=1);

namespace Tests\Logging;

use PHPUnit\Framework\TestCase;
use Sahil\Foundation\Application;
use Sahil\Log\Log;
use Sahil\Support\Logging\LoggerContract;
use Sahil\Support\Logging\LogManager;

/**
 * `Log::` against the real booted application: container-bound
 * LogManager, file channel writing JSONL, shared-context scope, and
 * redaction end to end.
 */
final class LogFlowTest extends TestCase
{
    private ?string $logFile = null;

    protected function tearDown(): void
    {
        Log::flushState();
        Application::free();
        if ($this->logFile !== null) {
            @unlink($this->logFile);
        }
        parent::tearDown();
    }

    public function test_facade_writes_structured_records_through_the_container(): void
    {
        $app = $this->boot();
        $this->logFile = sys_get_temp_dir().'/sahil-flow-'.bin2hex(random_bytes(6)).'.log';

        // Point the app-configured manager at a temp sink.
        $manager = new LogManager([
            'file' => new \Sahil\Support\Logging\FileLogger($this->logFile),
        ]);
        $app->container()->instance(LogManager::class, $manager);
        $app->container()->instance(LoggerContract::class, $manager);

        Log::info('user registered', ['user_id' => 9, 'password' => 'hunter2']);

        $event = json_decode(file_get_contents($this->logFile), true);
        self::assertSame('user registered', $event['message']);
        self::assertSame(9, $event['context']['user_id']);
        self::assertSame('[redacted]', $event['context']['password']);

        // Scope isolation in the same process.
        Log::scope(['request_id' => 'rid-1'], static function (): void {
            Log::warning('inside');
        });
        Log::warning('outside');

        $lines = file($this->logFile, FILE_IGNORE_NEW_LINES);
        self::assertSame('rid-1', json_decode($lines[1], true)['context']['request_id']);
        self::assertArrayNotHasKey('request_id', json_decode($lines[2], true)['context']);
    }

    public function test_configured_channels_exist(): void
    {
        $app = $this->boot();
        /** @var LogManager $manager */
        $manager = $app->container()->make(LogManager::class);

        // config/logging.php defaults — file channel resolves.
        self::assertInstanceOf(\Sahil\Support\Logging\FileLogger::class, $manager->channel('file'));
        self::assertSame($manager, $app->container()->make(LoggerContract::class));
    }

    private function boot(): Application
    {
        require_once dirname(__DIR__, 2).'/bootstrap/app.php';

        $app = \sahil_bootstrap();

        if (Application::peek() !== $app) {
            $configured = new \ReflectionProperty(Application::class, 'configured');
            $configured->setValue(null, $app);
        }

        return $app;
    }
}
