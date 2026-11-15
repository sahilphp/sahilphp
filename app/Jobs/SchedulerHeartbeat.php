<?php

declare(strict_types=1);

namespace App\Jobs;

use Sahil\Foundation\Application;
use Sahil\Queue\Jobs\Job;
use Sahil\Support\Logging\LoggerContract;

/**
 * Example scheduled job — dispatched onto the queue by
 * `schedule:run`; resolves its own dependencies when a worker picks it
 * up. Replace it with real maintenance work (prune sessions, generate
 * reports, sync data) as the application grows.
 */
final class SchedulerHeartbeat extends Job
{
    public function handle(): mixed
    {
        Application::peek()?->container()->make(LoggerContract::class)
            ->info('scheduler.heartbeat', ['ran_at' => date(DATE_ATOM)]);

        return null;
    }
}
