<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\UserRegistered;
use Sahil\Contracts\Events\QueueableContract;
use Sahil\Support\Logging\LoggerContract;

/**
 * Queued listener — `QueueableContract` opts it out of the dispatch call:
 * the event payload is serialized onto the existing queue and this
 * `handle()` runs when a worker picks the job up.
 */
final class RecordRegistrationActivity implements QueueableContract
{
    public function __construct(
        private readonly LoggerContract $logger,
    ) {
    }

    public function handle(UserRegistered $event): void
    {
        $this->logger->info('user.registered', [
            'notifiable' => $event->user::class,
        ]);
    }

    public function queueName(): ?string
    {
        return null;
    }

    public function queueDelay(): int
    {
        return 0;
    }
}
