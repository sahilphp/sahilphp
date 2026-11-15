<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\UserRegistered;
use App\Notifications\WelcomeNotification;

/**
 * Synchronous listener — runs inline during `Event::dispatch()`. Lands
 * the welcome notification in the `notifications` table where
 * `$user->unreadNotifications()` can read it.
 */
final class SendWelcomeNotification
{
    public function handle(UserRegistered $event): void
    {
        $event->user->notify(new WelcomeNotification());
    }
}
