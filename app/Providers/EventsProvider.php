<?php

declare(strict_types=1);

namespace App\Providers;

use App\Events\UserRegistered;
use App\Listeners\RecordRegistrationActivity;
use App\Listeners\SendWelcomeNotification;
use Sahil\Container\Container;
use Sahil\Events\Event;
use Sahil\Events\EventDispatcher;
use Sahil\Foundation\ServiceProvider;

/**
 * The application's event wiring: one shared dispatcher plus its
 * listener registrations.
 *
 * `Event::listen()` calls are idempotent per boot — `boot()` runs once
 * per process, and duplicate registrations throw rather than silently
 * double-firing.
 */
final class EventsProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app()->container()->singleton(
            EventDispatcher::class,
            static fn (Container $container): EventDispatcher => new EventDispatcher($container),
        );
    }

    public function boot(): void
    {
        Event::listen(UserRegistered::class, SendWelcomeNotification::class);
        Event::listen(UserRegistered::class, RecordRegistrationActivity::class);
    }
}
