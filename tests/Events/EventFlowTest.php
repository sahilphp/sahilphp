<?php

declare(strict_types=1);

namespace Tests\Events;

use App\Events\UserRegistered;
use App\Listeners\RecordRegistrationActivity;
use App\Listeners\SendWelcomeNotification;
use PHPUnit\Framework\TestCase;
use Sahil\Contracts\Notifications\NotifiableContract;
use Sahil\Contracts\Notifications\NotificationContract;
use Sahil\Contracts\Queue\DispatcherContract;
use Sahil\Contracts\Queue\Jobs\JobContract;
use Sahil\Events\Event;
use Sahil\Events\EventDispatcher;
use Sahil\Events\Jobs\InvokeListenerJob;
use Sahil\Foundation\Application;
use Sahil\Notifications\NotificationReport;

/**
 * End-to-end: the app's EventsProvider registers `UserRegistered`
 * listeners on the container-bound dispatcher — a sync listener sends
 * the welcome notification and a queued listener lands on the queue.
 */
final class EventFlowTest extends TestCase
{
    protected function tearDown(): void
    {
        Event::flushState();
        Application::free();

        parent::tearDown();
    }

    public function test_boot_registers_user_registered_listeners(): void
    {
        $this->boot();

        self::assertTrue(Event::hasListeners(UserRegistered::class));

        $listeners = Event::listeners(UserRegistered::class);
        self::assertContains(SendWelcomeNotification::class, $listeners);
        self::assertContains(RecordRegistrationActivity::class, $listeners);
    }

    public function test_dispatch_runs_sync_and_queued_listeners(): void
    {
        $app = $this->boot();

        $queue = new RecordingQueueDispatcher();
        $app->container()->instance(DispatcherContract::class, $queue);

        $user = new RecordingNotifiable();
        Event::dispatch(new UserRegistered($user));

        // Sync listener delivered the welcome notification inline.
        self::assertSame(1, $user->notifications);

        // Queued listener submitted exactly one InvokeListenerJob.
        self::assertCount(1, $queue->jobs);
        self::assertInstanceOf(InvokeListenerJob::class, $queue->jobs[0]);
    }

    private function boot(): Application
    {
        require_once dirname(__DIR__, 2).'/bootstrap/app.php';

        $app = \sahil_bootstrap();
        Event::swap($app->container()->make(EventDispatcher::class));

        // `sahil_bootstrap()` caches its instance — another test's
        // `Application::free()` may have dropped the configured handle
        // that `app()` (used by queued jobs) resolves through.
        if (Application::peek() !== $app) {
            $configured = new \ReflectionProperty(Application::class, 'configured');
            $configured->setValue(null, $app);
        }

        return $app;
    }
}

final class RecordingNotifiable implements NotifiableContract
{
    public int $notifications = 0;

    public function notify(NotificationContract $notification, bool $queue = false, int $delay = 0): NotificationReport|string
    {
        $this->notifications++;

        return new NotificationReport();
    }

    public function routeNotificationFor(string $channel): ?string
    {
        return null;
    }
}

final class RecordingQueueDispatcher implements DispatcherContract
{
    /** @var list<JobContract> */
    public array $jobs = [];

    public function dispatch(JobContract $job, int $delay = 0): string
    {
        $this->jobs[] = $job;

        return 'job-'.count($this->jobs);
    }

    public function pushOn(string $queue, JobContract $job, int $delay = 0): string
    {
        return $this->dispatch($job, $delay);
    }

    public function pushRaw(string $queue, string $payload, int $delay = 0): string
    {
        return 'raw';
    }
}
