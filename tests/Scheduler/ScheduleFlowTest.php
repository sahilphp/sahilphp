<?php

declare(strict_types=1);

namespace Tests\Scheduler;

use PHPUnit\Framework\TestCase;
use Sahil\Foundation\Application;
use Sahil\Scheduler\Schedule;
use Sahil\Scheduler\ScheduleRunner;

/**
 * The app's `routes/schedule.php` registers tasks on the container-bound
 * Schedule through the normal provider lifecycle.
 */
final class ScheduleFlowTest extends TestCase
{
    protected function tearDown(): void
    {
        Application::free();

        parent::tearDown();
    }

    public function test_schedule_loads_registered_tasks(): void
    {
        $app = $this->boot();

        /** @var Schedule $schedule */
        $schedule = $app->container()->make(Schedule::class);

        self::assertNotNull($schedule->task('scheduler-heartbeat'));
        self::assertNotNull($schedule->task('prune-stale-locks'));
        self::assertTrue($schedule->task('scheduler-heartbeat')?->preventsOverlap());
    }

    public function test_runner_evaluates_due_tasks_without_running_undue_ones(): void
    {
        $app = $this->boot();

        /** @var ScheduleRunner $runner */
        $runner = $app->container()->make(ScheduleRunner::class);

        // 23:02 — nothing due (heartbeat is */15).
        $report = $runner->runAt(new \DateTimeImmutable('2026-11-14 23:02', new \DateTimeZone('UTC')));
        self::assertSame([], $report->results());
    }

    private function boot(): Application
    {
        require_once dirname(__DIR__, 2).'/bootstrap/app.php';

        return \sahil_bootstrap();
    }
}
