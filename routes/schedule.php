<?php

declare(strict_types=1);

use Sahil\Scheduler\Schedule;

/**
 * Scheduled tasks for this installation.
 *
 * `php sahil schedule:run` evaluates this file on every invocation —
 * wire it to run each minute from system cron:
 *
 *     * * * * * cd /path/to/app && php sahil schedule:run >> /dev/null 2>&1
 *
 * `$schedule->job()` hands the job to the queue when due (retries and
 * failed-job handling stay with the queue); `jobSync()` and `call()`
 * run inline in the scheduler process.
 */
return static function (Schedule $schedule): void {
    // Queued work — a worker picks it up.
    $schedule->job(new \App\Jobs\SchedulerHeartbeat())
        ->everyFifteenMinutes()
        ->withoutOverlapping();

    // Inline maintenance — cheap tasks that don't need a worker.
    $schedule->call('prune-stale-locks', static function (): void {
        // e.g. clear expired scheduler lock files.
    })->daily();
};
